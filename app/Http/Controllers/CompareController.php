<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use Illuminate\Support\Collection;
use App\Services\Memory\ConsentService;
use App\Services\Memory\MemoryEventService;
use Illuminate\Validation\ValidationException;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Contracts\NexusRetriever;

/**
 * Flow D: grounded side-by-side comparison — citations beating vibes.
 * Consent-gated, and only over the user's own saved or trailed works:
 * asking for a comparison is an enumerated explicit signal, so it goes in
 * the ledger (deduped — a refresh is not a second request, and pause
 * stops the write while the page still renders).
 */
class CompareController extends Controller
{
    public function __construct(
        private readonly NexusRetriever $retriever,
        private readonly ConsentService $consent,
        private readonly MemoryEventService $events,
    ) {}

    public function __invoke(Request $request): View
    {
        $validated = $this->validate($request, [
            'works' => ['required', 'array', 'min:2', 'max:4'],
            'works.*' => ['integer'],
            'trail' => ['nullable', 'integer'],
        ]);

        $user = $request->user();
        $requested = array_values(array_unique(array_map('intval', $validated['works'])));

        // The works must be the user's own — resolved, never trusted.
        $foreign = array_diff($requested, $this->comparableArtworkIds($user));

        if ($foreign !== []) {
            throw ValidationException::withMessages(['works' => 'Compare works from your saves or trails.']);
        }

        $found = Artwork::displayable()
            ->with(['artists', 'classification', 'department', 'medium', 'primaryImage', 'medias', 'slugs'])
            ->findMany($requested)
            ->keyBy('id');

        /** @var Collection<int, Artwork> $artworks in request order */
        $artworks = collect($requested)->map(fn(int $id) => $found->get($id))->filter()->values();

        if ($artworks->count() < 2) {
            throw ValidationException::withMessages(['works' => 'Pick at least two different works to compare.']);
        }

        $comparison = $this->retriever->compare($artworks->all());

        $this->recordRequest($user, $artworks);

        // Items come along so the per-work "Save to trail" affordance can
        // check membership without a query per compared work.
        $trail = isset($validated['trail'])
            ? $user->trails()->active()->with('items')->find((int) $validated['trail'])
            : null;

        return view('site.archive.compare', [
            'artworks' => $artworks,
            'comparison' => $comparison,
            'trail' => $trail,
        ]);
    }

    /**
     * @return list<int>
     */
    protected function comparableArtworkIds(User $user): array
    {
        return TrailItem::whereIn('research_trail_id', $user->trails()->select('id'))
            ->pluck('artwork_id')
            ->merge($user->artworkSaves()->pluck('artwork_id'))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Artwork>  $artworks
     */
    protected function recordRequest(User $user, Collection $artworks): void
    {
        // Pause stops writes, never reads.
        if ($this->consent->isPaused($user)) {
            return;
        }

        $ids = $artworks->pluck('id')->map(fn($id) => (int) $id)->values()->all();

        // A refresh of the same comparison inside the dedupe window is one
        // request, not several — the ledger records deliberate acts.
        $latest = $user
            ->memoryEvents()
            ->where('event_type', MemoryEvent::COMPARISON_REQUESTED)
            ->where('occurred_at', '>', now()->subMinutes((int) config('trails.revisit_dedupe_minutes')))
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->first();

        if ($latest !== null && array_map('intval', (array) ($latest->payload['artwork_ids'] ?? [])) === $ids) {
            return;
        }

        $this->events->record($user, MemoryEvent::COMPARISON_REQUESTED, null, [
            'artwork_ids' => $ids,
            'artwork_titles' => $artworks->pluck('title')->map(fn($title) => (string) $title)->values()->all(),
        ]);
    }
}
