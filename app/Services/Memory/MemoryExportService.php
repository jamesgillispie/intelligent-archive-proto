<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Models\Memory\UserNote;
use App\Models\Memory\TrailItem;
use App\Models\Memory\ArtworkSave;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\MemoryConsent;
use App\Models\Memory\ResearchTrail;
use App\Models\Memory\Recommendation;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * The user's whole memory as one structured, portable document: the SQL
 * record in full plus a verbatim snapshot of currently held Iris context.
 * Export is a right, not a settings burial — it works under paused, stale
 * or current consent alike, and contains nothing the dashboard doesn't
 * already show.
 */
class MemoryExportService
{
    public function __construct(
        private readonly RedisIrisMemoryContract $iris,
        private readonly VisitorContextService $visitors,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function export(User $user): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'agreements' => $user
                ->memoryConsents()
                ->orderBy('id')
                ->get()
                ->map(
                    fn(MemoryConsent $consent) => [
                        'consent_version' => $consent->consent_version,
                        'granted_at' => $consent->granted_at->toIso8601String(),
                        'revoked_at' => $consent->revoked_at?->toIso8601String(),
                    ],
                )
                ->all(),
            'memory_switches' => [
                'memory_paused' => (bool) $user->personalizationSetting?->memory_paused,
            ],
            'trails' => $user
                ->trails()
                ->with(['items.artwork', 'notes'])
                ->orderBy('id')
                ->get()
                ->map(fn(ResearchTrail $trail) => $this->trail($trail))
                ->all(),
            'saves' => $user
                ->artworkSaves()
                ->with('artwork')
                ->orderBy('id')
                ->get()
                ->map(
                    fn(ArtworkSave $save) => [
                        'artwork_title' => $save->artwork->title,
                        'nga_object_id' => $save->artwork->nga_object_id,
                        'saved_at' => $save->created_at->toIso8601String(),
                    ],
                )
                ->all(),
            // Every suggestion ever shown, verbatim — reason, evidence,
            // context, and what the user did with it.
            'suggestions' => $user
                ->recommendations()
                ->with(['artwork', 'trail'])
                ->orderBy('id')
                ->get()
                ->map(
                    fn(Recommendation $recommendation) => [
                        'artwork_title' => $recommendation->artwork?->title,
                        'trail_title' => $recommendation->trail?->title,
                        'kind' => $recommendation->kind,
                        'reason' => $recommendation->reason,
                        'status' => $recommendation->status,
                        'citations' => $recommendation->citations,
                        'context' => $recommendation->context,
                        'shown_at' => $recommendation->created_at->toIso8601String(),
                        'responded_at' => $recommendation->responded_at?->toIso8601String(),
                    ],
                )
                ->all(),
            'other_notes' => $user
                ->notes()
                ->where('notable_type', '!=', (new ResearchTrail())->getMorphClass())
                ->orderBy('id')
                ->get()
                ->map(fn(UserNote $note) => $this->note($note))
                ->all(),
            'ledger' => $user
                ->memoryEvents()
                ->orderBy('occurred_at')
                ->orderBy('id')
                ->get()
                ->map(
                    fn(MemoryEvent $event) => [
                        'event' => $event->event_type,
                        'description' => $event->description(),
                        'occurred_at' => $event->occurred_at->toIso8601String(),
                        'details' => $event->payload,
                    ],
                )
                ->all(),
            // What Redis Iris holds right now, verbatim — derived context
            // that would rebuild from the tables above. Trail summaries are
            // keyed by trail, so each trail's prefix is inspected too.
            'held_context' => array_merge(
                $this->iris->inspect($this->visitors->userPrefix($user)),
                ...$user
                    ->trails()
                    ->pluck('id')
                    ->map(fn($id) => $this->iris->inspect($this->visitors->trailPrefix((int) $id)))
                    ->all(),
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function trail(ResearchTrail $trail): array
    {
        return [
            'title' => $trail->title,
            'intent_note' => $trail->intent_note,
            'status' => $trail->status,
            'started_at' => $trail->created_at->toIso8601String(),
            'last_activity_at' => $trail->last_activity_at->toIso8601String(),
            'works' => $trail->items
                ->map(
                    fn(TrailItem $item) => [
                        'position' => $item->position,
                        'artwork_title' => $item->artwork->title,
                        'nga_object_id' => $item->artwork->nga_object_id,
                        'origin' => $item->origin,
                        'note' => $item->note,
                        'added_at' => $item->created_at->toIso8601String(),
                    ],
                )
                ->all(),
            'notes' => $trail->notes->map(fn(UserNote $note) => $this->note($note))->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function note(UserNote $note): array
    {
        return [
            'body' => $note->body,
            'written_at' => $note->created_at->toIso8601String(),
        ];
    }
}
