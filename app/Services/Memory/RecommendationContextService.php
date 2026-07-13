<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * The one component allowed to assemble personalization input (STRATEGY
 * §7), and it assembles almost nothing: the works whose suggestion the
 * user dismissed — an enumerated explicit action — so they are never
 * suggested again, anywhere. Held in Iris under
 * `user:{id}:recommendation_context` with a short TTL, fully derived from
 * SQL, rebuilt on demand, purged on pause and deletion, shown verbatim on
 * the dashboard.
 */
class RecommendationContextService
{
    public const RECOMMENDATION_CONTEXT = 'recommendation_context';

    public function __construct(
        private readonly RedisIrisMemoryContract $iris,
        private readonly VisitorContextService $visitors,
        private readonly ConsentService $consent,
    ) {}

    /**
     * Dismissal suppression is user-wide: dismissed once means never
     * re-suggested. Reads through Iris while memory may be written;
     * straight from SQL otherwise, so a paused user's dismissals are
     * still honored without holding derived context about them.
     *
     * @return list<int>
     */
    public function dismissedArtworkIds(User $user): array
    {
        if (!$this->consent->canRemember($user)) {
            return $this->fromSql($user);
        }

        $held = $this->iris->get($this->namespace($user));

        if ($held !== null) {
            return array_values(
                array_map('intval', array_filter((array) ($held['dismissed_artwork_ids'] ?? []), 'is_numeric')),
            );
        }

        return $this->rebuild($user);
    }

    /**
     * Called after a dismissal so suppression is immediate, not
     * TTL-eventual.
     */
    public function refresh(User $user): void
    {
        if ($this->consent->canRemember($user)) {
            $this->rebuild($user);
        }
    }

    /**
     * @return list<int>
     */
    protected function rebuild(User $user): array
    {
        $ids = $this->fromSql($user);

        $this->iris->put(
            $this->namespace($user),
            ['dismissed_artwork_ids' => $ids],
            (int) config('memory.recommendation_context_ttl_minutes') * 60,
        );

        return $ids;
    }

    /**
     * @return list<int>
     */
    protected function fromSql(User $user): array
    {
        return $user
            ->recommendations()
            ->dismissed()
            ->orderBy('artwork_id')
            ->distinct()
            ->pluck('artwork_id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    protected function namespace(User $user): string
    {
        return $this->visitors->userNamespace($user, self::RECOMMENDATION_CONTEXT);
    }
}
