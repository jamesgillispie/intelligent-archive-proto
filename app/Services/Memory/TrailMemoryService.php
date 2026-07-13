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

    protected function namespace(User $user): string
    {
        return $this->visitors->userNamespace($user, self::ACTIVE_TRAIL);
    }
}
