<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Models\Memory\ResearchTrail;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Maintains the user's `active_trail` pointer in Redis Iris — the context
 * behind "continue where you left off". The pointer is derived and
 * disposable: it only ever reflects ledgered SQL activity, and every read
 * is verified against SQL before anything renders. Writes are
 * consent-gated at write time; paused or stale-consent users fall back to
 * SQL alone.
 */
class TrailMemoryService
{
    public const ACTIVE_TRAIL = 'active_trail';

    public const SEMANTIC_SUMMARY = 'semantic_summary';

    public function __construct(
        private readonly RedisIrisMemoryContract $iris,
        private readonly VisitorContextService $visitors,
        private readonly ConsentService $consent,
    ) {}

    /**
     * Refresh the pointer after an explicit trail action. Called after the
     * SQL transaction commits — the row is already durable, Iris is only
     * catching up. A rolled-back write can at worst leave a stale pointer,
     * which the SQL verification on read makes harmless.
     */
    public function refreshActiveTrail(ResearchTrail $trail): void
    {
        if ($trail->isArchived() || !$this->consent->canRemember($trail->user)) {
            return;
        }

        $this->iris->put(
            $this->namespace($trail->user),
            [
                'trail_id' => $trail->id,
                'trail_title' => $trail->title,
                'item_count' => $trail->items()->count(),
                'last_activity_at' => $trail->last_activity_at->toIso8601String(),
            ],
            (int) config('memory.active_trail_ttl_days') * 86400,
        );
    }

    /**
     * Drop the pointer when the trail it points at stops being a place to
     * continue (archived or deleted). A pointer at some other trail is
     * left alone.
     */
    public function forgetActiveTrail(User $user, int $trailId): void
    {
        $held = $this->iris->get($this->namespace($user));

        if ($held !== null && (int) ($held['trail_id'] ?? 0) === $trailId) {
            $this->iris->forget($this->namespace($user));
        }
    }

    /**
     * Flow B: Iris supplies the hint, SQL supplies the truth. A missing,
     * expired or no-longer-valid pointer rebuilds from the most recent
     * active trail; while memory is paused nothing is read from or written
     * to Iris at all.
     */
    public function activeTrail(User $user): ?ResearchTrail
    {
        if ($this->consent->canRemember($user)) {
            $held = $this->iris->get($this->namespace($user));

            if ($held !== null) {
                $trail = $user->trails()->active()->withCount('items')->find((int) ($held['trail_id'] ?? 0));

                if ($trail !== null) {
                    // Refreshed on use, per the namespace contract.
                    $this->refreshActiveTrail($trail);

                    return $trail;
                }
            }
        }

        $trail = $user->trails()->active()->withCount('items')->orderByDesc('last_activity_at')->first();

        if ($trail !== null) {
            // Re-warm the pointer for next time (a quiet no-op unless
            // memory may be written right now).
            $this->refreshActiveTrail($trail);
        }

        return $trail;
    }

    /**
     * The compact, human-readable profile expansion queries run from —
     * derived entirely from the trail's works and their canonical
     * metadata. Content-hash keyed (STRATEGY §7): the held copy is only
     * trusted while the trail's items are unchanged; anything else
     * rebuilds. Written to Iris only while memory may be written;
     * otherwise computed fresh and held nowhere.
     *
     * @return array<string, mixed>
     */
    public function semanticSummary(ResearchTrail $trail): array
    {
        $artworkIds = $trail->items()->orderBy('position')->pluck('artwork_id')->map(fn($id) => (int) $id)->all();

        $hash = sha1(implode(',', $artworkIds));
        $namespace = $this->visitors->trailNamespace($trail->id, self::SEMANTIC_SUMMARY);
        $mayRemember = $this->consent->canRemember($trail->user);

        if ($mayRemember) {
            $held = $this->iris->get($namespace);

            if ($held !== null && ($held['content_hash'] ?? null) === $hash) {
                return $held;
            }
        }

        $summary = $this->buildSummary($trail, $artworkIds, $hash);

        if ($mayRemember) {
            $this->iris->put($namespace, $summary);
        }

        return $summary;
    }

    /**
     * Drop everything held for a trail that stopped being a working
     * surface (deleted or archived).
     */
    public function forgetTrailContext(int $trailId): void
    {
        $this->iris->purge($this->visitors->trailPrefix($trailId));
    }

    /**
     * @param  list<int>  $artworkIds
     * @return array<string, mixed>
     */
    protected function buildSummary(ResearchTrail $trail, array $artworkIds, string $hash): array
    {
        /** @var \Illuminate\Support\Collection<int, \App\Twill\Capsules\Artworks\Models\Artwork> $artworks */
        $artworks = $trail
            ->items()
            ->with(['artwork.artists', 'artwork.classification', 'artwork.medium'])
            ->get()
            ->pluck('artwork')
            ->filter()
            ->values();

        $years = $artworks->pluck('source_begin_year')->filter()->map(fn($year) => (int) $year);

        $names = fn($values) => $values->filter()->unique()->values()->all();

        // Anchors are stored by name, not id — the dashboard shows this
        // payload verbatim, so it has to read as what it is.
        return [
            'content_hash' => $hash,
            'trail_title' => $trail->title,
            'artwork_ids' => $artworkIds,
            'anchors' => [
                'artists' => $names($artworks->flatMap(fn($artwork) => $artwork->artists->pluck('name'))),
                'classifications' => $names($artworks->map(fn($artwork) => $artwork->classification?->name)),
                'mediums' => $names($artworks->map(fn($artwork) => $artwork->medium?->name ?? $artwork->medium_text)),
                'period' => $years->isEmpty() ? null : $years->min() . '–' . $years->max(),
            ],
        ];
    }

    protected function namespace(User $user): string
    {
        return $this->visitors->userNamespace($user, self::ACTIVE_TRAIL);
    }
}
