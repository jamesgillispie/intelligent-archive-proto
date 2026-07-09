<?php

namespace App\Services\Nexus;

use Illuminate\Support\Collection;
use App\Twill\Capsules\Artworks\Models\Artwork;

/**
 * Deterministic, in-process stand-in for the archive knowledge layer.
 *
 * Relatedness is computed from canonical metadata overlap (shared artist,
 * classification, medium, department, period), so every result is
 * explainable and cited from real fields. This defines the quality bar a
 * live client must meet; it involves no AI and no vendor calls.
 */
class FakeNexusArchive implements NexusArchiveContract
{
    /** @var array<int, true> */
    protected array $indexed = [];

    public function indexArtwork(Artwork $artwork): void
    {
        $this->indexed[$artwork->id] = true;
    }

    public function removeArtwork(int $artworkId): void
    {
        unset($this->indexed[$artworkId]);
    }

    /**
     * @return array<int, true> artwork ids indexed during this process
     */
    public function indexedArtworkIds(): array
    {
        return $this->indexed;
    }

    public function relatedTo(Artwork $artwork, ?int $limit = null): Collection
    {
        $limit ??= (int) config('nexus.retrieval_limit');
        $artistIds = $artwork->artists->pluck('id');

        $candidates = Artwork::displayable()
            ->with('artists')
            ->whereKeyNot($artwork->id)
            ->where(function ($query) use ($artwork, $artistIds) {
                $query
                    ->when(
                        $artistIds->isNotEmpty(),
                        fn($q) => $q->orWhereHas('artists', fn($sub) => $sub->whereIn('artists.id', $artistIds)),
                    )
                    ->when($artwork->classification_id, fn($q, $id) => $q->orWhere('classification_id', $id))
                    ->when($artwork->medium_id, fn($q, $id) => $q->orWhere('medium_id', $id))
                    ->when($artwork->department_id, fn($q, $id) => $q->orWhere('department_id', $id));
            })
            ->take(200)
            ->get();

        return $candidates
            ->map(fn(Artwork $candidate) => $this->match($artwork, $candidate, $artistIds))
            ->filter()
            ->sortByDesc(fn(ArchiveMatch $match) => $match->score)
            ->take($limit)
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, int>  $artistIds
     */
    protected function match(Artwork $artwork, Artwork $candidate, Collection $artistIds): ?ArchiveMatch
    {
        $score = 0.0;
        $reasons = [];
        $citations = [];

        $sharedArtists = $candidate->artists->whereIn('id', $artistIds);

        if ($sharedArtists->isNotEmpty()) {
            $score += 3.0;
            $reasons[] = 'also by ' . $sharedArtists->pluck('name')->join(', ');
            $citations[] = ['field' => 'artist', 'artwork_id' => $candidate->id];
        }

        if ($artwork->classification_id && $candidate->classification_id === $artwork->classification_id) {
            $score += 1.0;
            $citations[] = ['field' => 'classification', 'artwork_id' => $candidate->id];
        }

        if ($artwork->medium_id && $candidate->medium_id === $artwork->medium_id) {
            $score += 1.0;
            $reasons[] = 'same medium';
            $citations[] = ['field' => 'medium', 'artwork_id' => $candidate->id];
        }

        if ($artwork->department_id && $candidate->department_id === $artwork->department_id) {
            $score += 0.5;
            $citations[] = ['field' => 'department', 'artwork_id' => $candidate->id];
        }

        if (
            $artwork->source_begin_year &&
            $candidate->source_begin_year &&
            abs($artwork->source_begin_year - $candidate->source_begin_year) <= 25
        ) {
            $score += 1.0;
            $reasons[] = 'made within the same quarter century';
            $citations[] = ['field' => 'display_date', 'artwork_id' => $candidate->id];
        }

        if ($citations === [] || $score <= 0) {
            return null;
        }

        return new ArchiveMatch(
            artworkId: $candidate->id,
            reason: $reasons === [] ? 'related in the collection record' : ucfirst(implode('; ', $reasons)),
            score: $score,
            citations: $citations,
        );
    }
}
