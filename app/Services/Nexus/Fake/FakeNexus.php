<?php

namespace App\Services\Nexus\Fake;

use InvalidArgumentException;
use Illuminate\Support\Collection;
use App\Services\Nexus\Artifacts\Citation;
use App\Services\Nexus\Artifacts\TrailSummary;
use App\Services\Nexus\Contracts\NexusIndexer;
use App\Services\Nexus\Artifacts\ExploreResult;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Artifacts\CitationBundle;
use App\Services\Nexus\Artifacts\RelatedArtwork;
use App\Services\Nexus\Artifacts\TrailExpansion;
use App\Services\Nexus\Contracts\NexusRetriever;
use App\Services\Nexus\Artifacts\ArtworkDocument;
use App\Services\Nexus\Artifacts\ComparisonClaim;
use App\Services\Nexus\Artifacts\ArtworkComparison;
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
 *
 * @phpstan-type Anchors array{artist_ids: list<int>, classification_ids: list<int>, dominant_classification_id: int|null, medium_ids: list<int>, department_ids: list<int>, min_year: int|null, max_year: int|null}
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
                    foreach ($this->variants($term) as $variant) {
                        $like = '%' . $variant . '%';
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

    public function trailExpansion(TrailSummary $summary, ?int $limit = null): TrailExpansion
    {
        $limit ??= (int) config('nexus.retrieval_limit');

        $works = Artwork::displayable()
            ->with(['artists', 'classification', 'department', 'medium'])
            ->findMany($summary->artworkIds);

        if ($works->isEmpty()) {
            return new TrailExpansion(continuations: [], counterpoints: []);
        }

        $anchors = $this->anchors($works);
        $exclude = array_values(array_unique([...$summary->artworkIds, ...$summary->excludeArtworkIds]));

        // The split favors depth but always keeps room to broaden.
        $continuationLimit = max(1, (int) ceil(($limit * 2) / 3));
        $counterpointLimit = max(1, $limit - $continuationLimit);

        $continuations = $this->continuations($anchors, $exclude, $continuationLimit);

        $counterpoints = $this->counterpoints(
            $anchors,
            [...$exclude, ...array_map(fn(RelatedArtwork $match) => $match->artworkId, $continuations)],
            $counterpointLimit,
        );

        return new TrailExpansion(continuations: $continuations, counterpoints: $counterpoints);
    }

    /**
     * @param  list<Artwork>  $artworks two to four works
     */
    public function compare(array $artworks): ArtworkComparison
    {
        if (count($artworks) < 2) {
            throw new InvalidArgumentException('A comparison needs at least two works.');
        }

        $works = Collection::make($artworks)->each(
            fn(Artwork $work) => $work->loadMissing(['artists', 'classification', 'department', 'medium']),
        );

        $subject = $works->count() === 2 ? 'Both' : 'All ' . $works->count();
        $similarities = [];
        $differences = [];

        $this->compareArtists($works, $subject, $similarities, $differences);
        $this->compareField(
            $works,
            'classification',
            fn(Artwork $work) => $work->classification?->name,
            fn(string $value) => "{$subject} are classified as " . mb_strtolower($value),
            fn(string $values) => "Classified differently — {$values}",
            $similarities,
            $differences,
        );
        $this->compareField(
            $works,
            'medium',
            fn(Artwork $work) => $work->medium?->name ?? $work->medium_text,
            fn(string $value) => "{$subject} are {$value}",
            fn(string $values) => "Made in different mediums — {$values}",
            $similarities,
            $differences,
        );
        $this->compareField(
            $works,
            'department',
            fn(Artwork $work) => $work->department?->name,
            fn(string $value) => "{$subject} are held in {$value}",
            fn(string $values) => "Held in different areas of the collection — {$values}",
            $similarities,
            $differences,
        );
        $this->comparePeriod($works, $subject, $similarities, $differences);

        return new ArtworkComparison(
            artworkIds: $works->pluck('id')->all(),
            similarities: $similarities,
            differences: $differences,
        );
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
     * The works' shared profile in canonical terms: who, what kind of
     * work, what medium, where held, and when made.
     *
     * @param  Collection<int, Artwork>  $works
     * @return Anchors
     */
    protected function anchors(Collection $works): array
    {
        $classificationCounts = $works->pluck('classification_id')->filter()->map(fn($id) => (int) $id)->countBy();

        $years = $works->pluck('source_begin_year')->filter()->map(fn($year) => (int) $year);

        $ids = fn(string $column) => $works
            ->pluck($column)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return [
            'artist_ids' => $works
                ->flatMap(fn(Artwork $work) => $work->artists->pluck('id'))
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
            'classification_ids' => $ids('classification_id'),
            // Most frequent wins; ties break to the lowest id (stable sort
            // over ascending keys) so expansion stays deterministic.
            'dominant_classification_id' => $classificationCounts->isEmpty()
                ? null
                : (int) $classificationCounts->sortKeys()->sortDesc()->keys()->first(),
            'medium_ids' => $ids('medium_id'),
            'department_ids' => $ids('department_id'),
            'min_year' => $years->isEmpty() ? null : (int) $years->min(),
            'max_year' => $years->isEmpty() ? null : (int) $years->max(),
        ];
    }

    /**
     * @param  Anchors  $anchors
     * @param  list<int>  $exclude
     * @return list<RelatedArtwork>
     */
    protected function continuations(array $anchors, array $exclude, int $limit): array
    {
        if (
            $anchors['artist_ids'] === [] &&
            $anchors['classification_ids'] === [] &&
            $anchors['medium_ids'] === [] &&
            $anchors['department_ids'] === []
        ) {
            return [];
        }

        $candidates = Artwork::displayable()
            ->with(['artists', 'classification', 'department', 'medium'])
            ->whereNotIn('id', $exclude)
            ->where(function ($query) use ($anchors) {
                $query
                    ->when(
                        $anchors['artist_ids'] !== [],
                        fn($q) => $q->orWhereHas(
                            'artists',
                            fn($sub) => $sub->whereIn('artists.id', $anchors['artist_ids']),
                        ),
                    )
                    ->when(
                        $anchors['classification_ids'] !== [],
                        fn($q) => $q->orWhereIn('classification_id', $anchors['classification_ids']),
                    )
                    ->when($anchors['medium_ids'] !== [], fn($q) => $q->orWhereIn('medium_id', $anchors['medium_ids']))
                    ->when(
                        $anchors['department_ids'] !== [],
                        fn($q) => $q->orWhereIn('department_id', $anchors['department_ids']),
                    );
            })
            ->orderBy('id')
            ->take(200)
            ->get();

        return $candidates
            ->map(fn(Artwork $candidate) => $this->continuationMatch($candidate, $anchors))
            ->filter()
            ->sort(
                fn(RelatedArtwork $a, RelatedArtwork $b) => [$b->score, $a->artworkId] <=> [$a->score, $b->artworkId],
            )
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param  Anchors  $anchors
     */
    protected function continuationMatch(Artwork $candidate, array $anchors): ?RelatedArtwork
    {
        $score = 0.0;
        $reasons = [];
        $citations = [];

        $sharedArtists = $candidate->artists->whereIn('id', $anchors['artist_ids']);

        if ($sharedArtists->isNotEmpty()) {
            $score += 3.0;
            $names = $sharedArtists->pluck('name')->join(', ');
            $reasons[] = "also by {$names}";
            $citations[] = new Citation($candidate->id, 'artist', $names);
        }

        if (
            $candidate->classification_id &&
            in_array((int) $candidate->classification_id, $anchors['classification_ids'], true)
        ) {
            $score += 1.0;
            $citations[] = new Citation($candidate->id, 'classification', (string) $candidate->classification?->name);
        }

        if ($candidate->medium_id && in_array((int) $candidate->medium_id, $anchors['medium_ids'], true)) {
            $score += 1.0;
            $reasons[] = 'same medium';
            $citations[] = new Citation(
                $candidate->id,
                'medium',
                (string) ($candidate->medium?->name ?? $candidate->medium_text),
            );
        }

        if ($candidate->department_id && in_array((int) $candidate->department_id, $anchors['department_ids'], true)) {
            $score += 0.5;
            $citations[] = new Citation($candidate->id, 'department', (string) $candidate->department?->name);
        }

        if (
            $anchors['min_year'] !== null &&
            $anchors['max_year'] !== null &&
            $candidate->source_begin_year &&
            $candidate->source_begin_year >= $anchors['min_year'] - 25 &&
            $candidate->source_begin_year <= $anchors['max_year'] + 25
        ) {
            $score += 1.0;
            $reasons[] = 'made in the same period as these works';
            $citations[] = new Citation(
                $candidate->id,
                'display_date',
                (string) ($candidate->display_date ?? $candidate->source_begin_year),
            );
        }

        if ($citations === [] || $reasons === []) {
            // A continuation must have a nameable connection (artist,
            // medium, period) — classification overlap alone is too weak
            // to deepen a thread, and those works are exactly the pool
            // counterpoints draw from.
            return null;
        }

        return new RelatedArtwork(
            artworkId: $candidate->id,
            kind: RelatedArtwork::KIND_CONTINUATION,
            reason: ucfirst(implode('; ', $reasons)),
            score: $score,
            citations: new CitationBundle($citations),
        );
    }

    /**
     * Deliberate broadening: still recognizably kin (the dominant
     * classification is the bridge) but by other hands and contrasting in
     * medium or period — and the reason says so plainly.
     *
     * @param  Anchors  $anchors
     * @param  list<int>  $exclude
     * @return list<RelatedArtwork>
     */
    protected function counterpoints(array $anchors, array $exclude, int $limit): array
    {
        if ($anchors['dominant_classification_id'] === null) {
            return [];
        }

        $candidates = Artwork::displayable()
            ->with(['artists', 'classification', 'department', 'medium'])
            ->whereNotIn('id', $exclude)
            ->where('classification_id', $anchors['dominant_classification_id'])
            ->when(
                $anchors['artist_ids'] !== [],
                fn($q) => $q->whereDoesntHave(
                    'artists',
                    fn($sub) => $sub->whereIn('artists.id', $anchors['artist_ids']),
                ),
            )
            ->orderBy('id')
            ->take(200)
            ->get();

        return $candidates
            ->map(fn(Artwork $candidate) => $this->counterpointMatch($candidate, $anchors))
            ->filter()
            ->sort(
                fn(RelatedArtwork $a, RelatedArtwork $b) => [$b->score, $a->artworkId] <=> [$a->score, $b->artworkId],
            )
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param  Anchors  $anchors
     */
    protected function counterpointMatch(Artwork $candidate, array $anchors): ?RelatedArtwork
    {
        // The shared classification is the bridge that keeps a counterpoint
        // readable rather than random.
        $score = 1.0;
        $contrasts = [];
        $citations = [new Citation($candidate->id, 'classification', (string) $candidate->classification?->name)];

        if ($candidate->medium_id && !in_array((int) $candidate->medium_id, $anchors['medium_ids'], true)) {
            $score += 1.5;
            $medium = (string) ($candidate->medium?->name ?? $candidate->medium_text);
            $contrasts[] = "in a different medium ({$medium})";
            $citations[] = new Citation($candidate->id, 'medium', $medium);
        }

        if (
            $anchors['min_year'] !== null &&
            $anchors['max_year'] !== null &&
            $candidate->source_begin_year &&
            ($candidate->source_begin_year < $anchors['min_year'] - 50 ||
                $candidate->source_begin_year > $anchors['max_year'] + 50)
        ) {
            $score += 1.5;
            $date = (string) ($candidate->display_date ?? $candidate->source_begin_year);
            $contrasts[] = "from a different period ({$date})";
            $citations[] = new Citation($candidate->id, 'display_date', $date);
        }

        if ($contrasts === []) {
            // Kin with nothing to contrast is a continuation, not a
            // counterpoint.
            return null;
        }

        $classification = mb_strtolower((string) $candidate->classification?->name);

        return new RelatedArtwork(
            artworkId: $candidate->id,
            kind: RelatedArtwork::KIND_COUNTERPOINT,
            reason: "A counterpoint: still {$classification}, but " . implode(' and ', $contrasts),
            score: $score,
            citations: new CitationBundle($citations),
        );
    }

    /**
     * @param  Collection<int, Artwork>  $works
     * @param  list<ComparisonClaim>  $similarities
     * @param  list<ComparisonClaim>  $differences
     */
    protected function compareArtists(
        Collection $works,
        string $subject,
        array &$similarities,
        array &$differences,
    ): void {
        $first = $works->first();

        if ($first === null || $works->contains(fn(Artwork $work) => $work->artists->isEmpty())) {
            // A claim needs a name for every work.
            return;
        }

        $shared = $works
            ->skip(1)
            ->reduce(
                fn(Collection $carry, Artwork $work) => $carry->intersect($work->artists->pluck('id')),
                $first->artists->pluck('id'),
            );

        $citations = new CitationBundle(
            $works
                ->map(fn(Artwork $work) => new Citation($work->id, 'artist', $work->artists->pluck('name')->join(', ')))
                ->values()
                ->all(),
        );

        if ($shared->isNotEmpty()) {
            $names = $first->artists->whereIn('id', $shared->all())->pluck('name')->join(', ');
            $similarities[] = new ComparisonClaim("{$subject} are by {$names}", $citations);
        } else {
            $names = $works->map(fn(Artwork $work) => $work->artists->pluck('name')->join(', '))->join('; ');
            $differences[] = new ComparisonClaim("By different hands — {$names}", $citations);
        }
    }

    /**
     * One canonical field compared across every work: the same everywhere
     * becomes a similarity, anything else a difference, and either way the
     * claim quotes each work's actual value.
     *
     * @param  Collection<int, Artwork>  $works
     * @param  callable(Artwork): (string|null)  $value
     * @param  callable(string): string  $whenShared
     * @param  callable(string): string  $whenDifferent
     * @param  list<ComparisonClaim>  $similarities
     * @param  list<ComparisonClaim>  $differences
     */
    protected function compareField(
        Collection $works,
        string $field,
        callable $value,
        callable $whenShared,
        callable $whenDifferent,
        array &$similarities,
        array &$differences,
    ): void {
        $values = $works->map(fn(Artwork $work) => trim((string) $value($work)))->values();

        if ($values->contains('')) {
            // A claim needs evidence from every work.
            return;
        }

        $citations = new CitationBundle(
            $works
                ->values()
                ->map(fn(Artwork $work, int $index) => new Citation($work->id, $field, (string) $values[$index]))
                ->all(),
        );

        if ($values->unique()->count() === 1) {
            $similarities[] = new ComparisonClaim($whenShared((string) $values->first()), $citations);
        } else {
            $differences[] = new ComparisonClaim($whenDifferent($values->unique()->join('; ')), $citations);
        }
    }

    /**
     * @param  Collection<int, Artwork>  $works
     * @param  list<ComparisonClaim>  $similarities
     * @param  list<ComparisonClaim>  $differences
     */
    protected function comparePeriod(
        Collection $works,
        string $subject,
        array &$similarities,
        array &$differences,
    ): void {
        if ($works->contains(fn(Artwork $work) => !$work->source_begin_year)) {
            return;
        }

        $years = $works->map(fn(Artwork $work) => (int) $work->source_begin_year);
        $gap = (int) $years->max() - (int) $years->min();

        $citations = new CitationBundle(
            $works
                ->map(
                    fn(Artwork $work) => new Citation(
                        $work->id,
                        'display_date',
                        (string) ($work->display_date ?? $work->source_begin_year),
                    ),
                )
                ->values()
                ->all(),
        );

        if ($gap <= 25) {
            $statement =
                $gap === 0
                    ? "{$subject} were made in the same year ({$years->first()})"
                    : "{$subject} were made within {$gap} years of each other";
            $similarities[] = new ComparisonClaim($statement, $citations);
        } else {
            $differences[] = new ComparisonClaim("Made about {$gap} years apart", $citations);
        }
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
                $matched = $haystack === '' ? null : $this->firstMatchingVariant($haystack, $term);

                if ($matched === null) {
                    continue;
                }

                $score += $weight;
                $reasons[] = $describe($matched);
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
     * Naive singular/plural folding so "storms" still finds "Storm at
     * Sea". Substring matching already covers singular query → plural
     * text; these fallback stems cover the other direction, and a crude
     * stem is fine here because it is matched as a substring ("cit[y]"
     * from "cities", "box" from "boxes"). A live semantic client makes
     * this whole concern moot.
     *
     * @return non-empty-list<string> the term itself first, then any fallback stem
     */
    protected function variants(string $term): array
    {
        $variants = [$term];

        if (mb_strlen($term) >= 5 && str_ends_with($term, 'ies')) {
            $variants[] = mb_substr($term, 0, -3) . 'y';
        } elseif (mb_strlen($term) >= 5 && str_ends_with($term, 'es')) {
            $variants[] = mb_substr($term, 0, -2);
        } elseif (mb_strlen($term) >= 4 && str_ends_with($term, 's') && !str_ends_with($term, 'ss')) {
            $variants[] = mb_substr($term, 0, -1);
        }

        return $variants;
    }

    protected function firstMatchingVariant(string $haystack, string $term): ?string
    {
        foreach ($this->variants($term) as $variant) {
            if (mb_stripos($haystack, $variant) !== false) {
                return $variant;
            }
        }

        return null;
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
