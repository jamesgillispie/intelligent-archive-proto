<?php

namespace App\Services\Trails;

use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use Illuminate\Support\Facades\DB;
use App\Models\Memory\ResearchTrail;
use App\Models\Memory\Recommendation;
use App\Services\Nexus\Artifacts\Citation;
use App\Services\Memory\MemoryEventService;
use App\Services\Memory\TrailMemoryService;
use App\Services\Nexus\Artifacts\TrailSummary;
use Illuminate\Validation\ValidationException;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Contracts\NexusRetriever;
use App\Services\Memory\RecommendationContextService;

/**
 * Flow C orchestration — the only place visitor context and archive
 * knowledge meet. Iris supplies the trail summary and the dismissals to
 * respect; Nexus receives a neutral summary and returns cited candidates;
 * SQL keeps every shown suggestion as a Recommendation row, so "why this
 * suggestion?" always answers from what actually happened.
 */
class TrailExpansionService
{
    public function __construct(
        private readonly NexusRetriever $retriever,
        private readonly TrailMemoryService $trailMemory,
        private readonly RecommendationContextService $recommendationContext,
        private readonly TrailService $trails,
        private readonly MemoryEventService $events,
    ) {}

    /**
     * @return list<Recommendation>
     */
    public function expand(ResearchTrail $trail): array
    {
        if ($trail->isArchived()) {
            throw ValidationException::withMessages(['trail' => 'Reactivate this trail to expand it.']);
        }

        $minItems = (int) config('trails.expansion_min_items');

        if ($trail->items()->count() < $minItems) {
            throw ValidationException::withMessages([
                'trail' => "Save at least {$minItems} works to this trail first — suggestions grow from what you chose.",
            ]);
        }

        $summary = $this->trailMemory->semanticSummary($trail);
        $dismissed = $this->recommendationContext->dismissedArtworkIds($trail->user);

        // Anything ever suggested for this trail stays excluded: open rows
        // keep rendering until answered, accepted works are in the trail,
        // and dismissals are suppressed user-wide.
        $alreadySuggested = $trail->recommendations()->pluck('artwork_id')->map(fn($id) => (int) $id)->all();

        $expansion = $this->retriever->trailExpansion(
            new TrailSummary(
                artworkIds: array_map('intval', (array) $summary['artwork_ids']),
                excludeArtworkIds: array_values(array_unique([...$alreadySuggested, ...$dismissed])),
            ),
            (int) config('trails.suggestions_per_expansion'),
        );

        // The snapshot behind "why this suggestion?" — what informed the
        // query at the moment it ran, in the user's own terms.
        $context = [
            'trail_title' => $trail->title,
            'item_count' => count((array) $summary['artwork_ids']),
            'anchors' => $summary['anchors'],
            'dismissals_respected' => count($dismissed),
        ];

        return DB::transaction(function () use ($trail, $expansion, $context) {
            $created = [];

            foreach ($expansion->all() as $suggestion) {
                $created[] = $trail->user->recommendations()->create([
                    'research_trail_id' => $trail->id,
                    'artwork_id' => $suggestion->artworkId,
                    'kind' => $suggestion->kind,
                    'reason' => $suggestion->reason,
                    'citations' => array_map(
                        fn(Citation $citation) => $citation->toArray(),
                        iterator_to_array($suggestion->citations),
                    ),
                    'context' => $context,
                    'status' => Recommendation::STATUS_SHOWN,
                ]);
            }

            return $created;
        });
    }

    /**
     * Accepting is the explicit act of adding to the trail — the ledger
     * row comes from TrailService::addArtwork, with an origin that keeps
     * the suggestion's influence inspectable forever.
     */
    public function accept(Recommendation $recommendation): TrailItem
    {
        $this->assertOpen($recommendation);

        $trail = $recommendation->trail;

        if ($trail === null || $trail->isArchived()) {
            throw ValidationException::withMessages(['suggestion' => 'This trail is no longer active.']);
        }

        $artwork = Artwork::displayable()->findOrFail($recommendation->artwork_id);

        return DB::transaction(function () use ($recommendation, $trail, $artwork) {
            $item = $this->trails->addArtwork($trail, $artwork, null, TrailItem::ORIGIN_SUGGESTION_ACCEPTED);

            $recommendation->update([
                'status' => Recommendation::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            return $item;
        });
    }

    /**
     * A dismissal is an enumerated explicit signal: it goes in the ledger,
     * and the work is never suggested again, in any trail.
     */
    public function dismiss(Recommendation $recommendation): void
    {
        $this->assertOpen($recommendation);

        DB::transaction(function () use ($recommendation) {
            $recommendation->update([
                'status' => Recommendation::STATUS_DISMISSED,
                'responded_at' => now(),
            ]);

            $this->events->record($recommendation->user, MemoryEvent::SUGGESTION_DISMISSED, $recommendation, [
                'artwork_title' => (string) $recommendation->artwork?->title,
                'trail_title' => (string) $recommendation->trail?->title,
            ]);
        });

        // Suppression is immediate, not TTL-eventual.
        $this->recommendationContext->refresh($recommendation->user);
    }

    protected function assertOpen(Recommendation $recommendation): void
    {
        if (!$recommendation->isOpen()) {
            throw ValidationException::withMessages(['suggestion' => 'This suggestion was already answered.']);
        }
    }
}
