<?php

namespace App\Http\Controllers\Traits;

use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Classifications\Models\Classification;

/**
 * Deterministic, non-personalized starting points for the explore
 * surface, drawn from the canonical record: the kinds of work and the
 * names most present in the displayable collection. The same for every
 * visitor — these are the archive's suggestions, never the visitor's
 * history.
 */
trait SuggestsStartingPoints
{
    /**
     * @return list<string>
     */
    protected function startingPoints(int $limit = 8): array
    {
        $perFacet = max(1, (int) ceil($limit / 2));

        $classifications = Classification::publishedInListings()
            ->withCount(['artworks as displayable_artworks_count' => fn($query) => $query->displayable()])
            ->get();

        $artists = Artist::publishedInListings()
            ->withCount(['artworks as displayable_artworks_count' => fn($query) => $query->displayable()])
            ->get();

        $names = fn($vocabulary) => $vocabulary
            ->filter(fn($entry) => $entry->displayable_artworks_count > 0 && filled($entry->name))
            ->sortBy([['displayable_artworks_count', 'desc'], ['name', 'asc']])
            ->take($perFacet)
            ->pluck('name')
            ->map(fn($name) => (string) $name);

        return $names($classifications)->merge($names($artists))->unique()->take($limit)->values()->all();
    }
}
