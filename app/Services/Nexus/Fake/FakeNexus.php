<?php

namespace App\Services\Nexus\Fake;

use Illuminate\Support\Collection;
use App\Services\Nexus\Artifacts\Citation;
use App\Services\Nexus\Contracts\NexusIndexer;
use App\Services\Nexus\Artifacts\ExploreResult;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Artifacts\CitationBundle;
use App\Services\Nexus\Artifacts\RelatedArtwork;
use App\Services\Nexus\Contracts\NexusRetriever;
use App\Services\Nexus\Artifacts\ArtworkDocument;
use App\Services\Nexus\Artifacts\RelatedArtworkSet;

/**
 * Deterministic, in-process stand-in for the archive knowledge layer.
 * No AI, no vendor calls; this defines the behavioral bar a live client
 * must meet — every result explained and cited from real canonical fields.
 *
 * Indexing records documents in memory so the pipeline (document building,
 * idempotence, removal) is fully testable. Retrieval reads the canonical
 * database directly rather than the recorded upserts: an in-process index
 * cannot span the queue worker and the web request, and the canonical DB
 * *is* this fake's document store. A live client will retrieve over its
 * own index; the contract seam is identical either way.
 */
class FakeNexus implements NexusIndexer, NexusRetriever
{
    /** @var array<int, ArtworkDocument> keyed by artwork id */
    protected array $documents = [];

    public function upsertArtwork(ArtworkDocument $document): void
    {
        $this->documents[$document->artworkId] = $document;
    }

    public function removeArtwork(int $artworkId): void
    {
        unset($this->documents[$artworkId]);
    }

    /**
     * @return array<int, ArtworkDocument> documents indexed during this process
     */
    public function documents(): array
    {
        return $this->documents;
    }

    public function relatedTo(Artwork $artwork, ?int $limit = null): RelatedArtworkSet
    {
        $limit ??= (int) config('nexus.retrieval_limit');
        $artistIds = $artwork->artists->pluck('id');

        $candidates = Artwork::displayable()
            ->with(['artists', 'classification', 'department', 'medium'])
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
            ->orderBy('id')
            ->take(200)
            ->get();

        $related = $candidates
            ->map(fn(Artwork $candidate) => $this->matchAgainst($artwork, $candidate, $artistIds))
            ->filter()
            ->sort(
                fn(RelatedArtwork $a, RelatedArtwork $b) => [$b->score, $a->artworkId] <=> [$a->score, $b->artworkId],
            )
            ->take($limit)
            ->values()
            ->all();

        return new RelatedArtworkSet(subjectArtworkId: $artwork->id, related: $related);
    }

    public function explore(string $query, ?int $limit = null): ExploreResult
    {
        $limit ??= (int) config('nexus.retrieval_limit');
        $terms = $this->terms($query);

        if ($terms === []) {
            return new ExploreResult(query: $query, matches: []);
        }

        $candidates = Artwork::displayable()
            ->with(['artists', 'classification', 'department', 'medium'])
            ->where(function ($outer) use ($terms) {
                foreach ($terms as $term) {
                    $like = '%' . $term . '%';
                    $outer
                        ->orWhere('title', 'ilike', $like)
                        ->orWhere('attribution', 'ilike', $like)
                        ->orWhere('medium_text', 'ilike', $like)
                        ->orWhere('description', 'ilike', $like)
                        ->orWhere('source_inscription', 'ilike', $like)
                        ->orWhereHas('artists', fn($q) => $q->where('name', 'ilike', $like))
                        ->orWhereHas('classification', fn($q) => $q->where('name', 'ilike', $like))
                        ->orWhereHas('department', fn($q) => $q->where('name', 'ilike', $like))
                        ->orWhereHas('medium', fn($q) => $q->where('name', 'ilike', $like));
                }
            })
            ->orderBy('id')
            ->take(200)
            ->get();

        $matches = $candidates
            ->map(fn(Artwork $candidate) => $this->queryMatch($candidate, $terms))
            ->filter()
            ->sort(
                fn(RelatedArtwork $a, RelatedArtwork $b) => [$b->score, $a->artworkId] <=> [$a->score, $b->artworkId],
            )
            ->take($limit)
            ->values()
            ->all();

        return new ExploreResult(query: $query, matches: $matches);
    }

    /**
     * @param  Collection<int, int>  $artistIds
     */
    protected function matchAgainst(Artwork $artwork, Artwork $candidate, Collection $artistIds): ?RelatedArtwork
    {
        $score = 0.0;
        $reasons = [];
        $citations = [];
        $sharedArtist = false;

        $sharedArtists = $candidate->artists->whereIn('id', $artistIds);

        if ($sharedArtists->isNotEmpty()) {
            $sharedArtist = true;
            $score += 3.0;
            $names = $sharedArtists->pluck('name')->join(', ');
            $reasons[] = "also by {$names}";
            $citations[] = new Citation($candidate->id, 'artist', $names);
        }

        if ($artwork->classification_id && $candidate->classification_id === $artwork->classification_id) {
            $score += 1.0;
            $citations[] = new Citation($candidate->id, 'classification', (string) $candidate->classification?->name);
        }

        if ($artwork->medium_id && $candidate->medium_id === $artwork->medium_id) {
            $score += 1.0;
            $reasons[] = 'same medium';
            $citations[] = new Citation(
                $candidate->id,
                'medium',
                (string) ($candidate->medium?->name ?? $candidate->medium_text),
            );
        }

        if ($artwork->department_id && $candidate->department_id === $artwork->department_id) {
            $score += 0.5;
            $citations[] = new Citation($candidate->id, 'department', (string) $candidate->department?->name);
        }

        if (
            $artwork->source_begin_year &&
            $candidate->source_begin_year &&
            abs($artwork->source_begin_year - $candidate->source_begin_year) <= 25
        ) {
            $score += 1.0;
            $reasons[] = 'made within the same quarter century';
            $citations[] = new Citation(
                $candidate->id,
                'display_date',
                (string) ($candidate->display_date ?? $candidate->source_begin_year),
            );
        }

        if ($citations === [] || $score <= 0) {
            return null;
        }

        return new RelatedArtwork(
            artworkId: $candidate->id,
            kind: $sharedArtist ? RelatedArtwork::KIND_SHARED_ARTIST : RelatedArtwork::KIND_SHARED_METADATA,
            reason: $reasons === [] ? 'Related in the collection record' : ucfirst(implode('; ', $reasons)),
            score: $score,
            citations: new CitationBundle($citations),
        );
    }

    /**
     * @param  list<string>  $terms
     */
    protected function queryMatch(Artwork $candidate, array $terms): ?RelatedArtwork
    {
        $score = 0.0;
        $reasons = [];
        $citations = [];

        $haystacks = [
            'title' => [3.0, (string) $candidate->title, fn($t) => "the title mentions '{$t}'"],
            'artist' => [2.5, $candidate->artists->pluck('name')->join(', '), fn($t) => "by an artist matching '{$t}'"],
            'description' => [
                2.0,
                trim(strip_tags((string) $candidate->description)),
                fn($t) => "the description mentions '{$t}'",
            ],
            'medium' => [
                1.5,
                (string) ($candidate->medium?->name ?? $candidate->medium_text),
                fn($t) => "the medium mentions '{$t}'",
            ],
            'classification' => [
                1.5,
                (string) $candidate->classification?->name,
                fn($t) => 'classified as ' . mb_strtolower((string) $candidate->classification?->name),
            ],
            'attribution' => [1.0, (string) $candidate->attribution, fn($t) => "attributed to a name matching '{$t}'"],
            'inscription' => [
                0.75,
                (string) $candidate->source_inscription,
                fn($t) => "the inscription mentions '{$t}'",
            ],
            'department' => [
                0.75,
                (string) $candidate->department?->name,
                fn($t) => 'held in ' . (string) $candidate->department?->name,
            ],
        ];

        foreach ($terms as $term) {
            foreach ($haystacks as $field => [$weight, $haystack, $describe]) {
                if ($haystack === '' || mb_stripos($haystack, $term) === false) {
                    continue;
                }

                $score += $weight;
                $reasons[] = $describe($term);
                $citations[] = new Citation($candidate->id, $field, $haystack);
            }
        }

        if ($citations === []) {
            return null;
        }

        return new RelatedArtwork(
            artworkId: $candidate->id,
            kind: RelatedArtwork::KIND_QUERY_MATCH,
            reason: ucfirst(implode('; ', array_values(array_unique($reasons)))),
            score: $score,
            citations: new CitationBundle($this->dedupeCitations($citations)),
        );
    }

    /**
     * Query terms: lowercased words of three letters or more, first six.
     *
     * @return list<string>
     */
    protected function terms(string $query): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower(trim($query))) ?: [];

        $terms = array_values(array_unique(array_filter($words, fn(string $word) => mb_strlen($word) >= 3)));

        return array_slice($terms, 0, 6);
    }

    /**
     * A term hitting the same field twice cites it once.
     *
     * @param  list<Citation>  $citations
     * @return list<Citation>
     */
    protected function dedupeCitations(array $citations): array
    {
        $seen = [];
        $unique = [];

        foreach ($citations as $citation) {
            $key = "{$citation->artworkId}:{$citation->field}";

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $citation;
        }

        return $unique;
    }
}
