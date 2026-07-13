<?php

namespace App\Services\Memory;

use App\Models\User;
use RuntimeException;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use Illuminate\Support\Facades\DB;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Whole-memory deletion (flow F). One transaction removes everything the
 * SQL record holds, a minimal receipt notes that the deletion happened,
 * and the user's Iris context is purged with a read-back verification —
 * a deletion that cannot prove itself is treated as a failure, loudly.
 */
class MemoryDeletionService
{
    public function __construct(
        private readonly MemoryEventService $events,
        private readonly RedisIrisMemoryContract $iris,
        private readonly VisitorContextService $visitors,
    ) {}

    /**
     * What a deletion would remove right now — the confirmation page shows
     * these numbers so consent to delete is as informed as consent to keep.
     *
     * @return array{trails: int, works: int, saves: int, notes: int, suggestions: int, entries: int}
     */
    public function counts(User $user): array
    {
        return [
            'trails' => $user->trails()->count(),
            'works' => TrailItem::whereIn('research_trail_id', $user->trails()->select('id'))->count(),
            'saves' => $user->artworkSaves()->count(),
            'notes' => $user->notes()->count(),
            'suggestions' => $user->recommendations()->count(),
            'entries' => $user->memoryEvents()->count(),
        ];
    }

    /**
     * @return array{trails: int, works: int, saves: int, notes: int, suggestions: int, entries: int}
     */
    public function deleteAll(User $user): array
    {
        // Captured before the rows go: trail-keyed Iris namespaces have to
        // be purged even though their trails no longer exist.
        $trailIds = $user->trails()->pluck('id')->map(fn($id) => (int) $id)->all();

        $counts = DB::transaction(function () use ($user) {
            $counts = $this->counts($user);

            $user->notes()->delete();
            $user->recommendations()->delete();
            $user->trails()->delete(); // items cascade at the database level
            $user->artworkSaves()->delete();
            $user->memoryEvents()->delete();

            // The receipt outlives the deletion: minimal ("this much was
            // deleted, now"), and itself deletable like any ledger row.
            $this->events->record($user, MemoryEvent::MEMORY_DELETED, null, $counts);

            return $counts;
        });

        $prefixes = [
            $this->visitors->userPrefix($user),
            ...array_map(fn(int $trailId) => $this->visitors->trailPrefix($trailId), $trailIds),
        ];

        foreach ($prefixes as $prefix) {
            $this->iris->purge($prefix);

            if ($this->iris->inspect($prefix) !== []) {
                throw new RuntimeException(
                    "Iris purge verification failed for prefix [{$prefix}]: held context remains after purge.",
                );
            }
        }

        return $counts;
    }
}
