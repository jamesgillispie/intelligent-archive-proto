<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\View\Factory;
use Illuminate\Validation\Rule;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Mediums\Models\Medium;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Twill\Capsules\Departments\Models\Department;
use App\Twill\Capsules\Classifications\Models\Classification;

class ArtworksController extends Controller
{
    public function index(): Factory|View
    {
        $filters = $this->validate(request(), [
            'artist' => ['nullable', Rule::exists(Artist::class, 'id')],
            'classification' => ['nullable', Rule::exists(Classification::class, 'id')],
            'department' => ['nullable', Rule::exists(Department::class, 'id')],
            'medium' => ['nullable', Rule::exists(Medium::class, 'id')],
            'year_from' => ['nullable', 'integer', 'between:1000,2100'],
            'year_to' => ['nullable', 'integer', 'between:1000,2100'],
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $artworks = Artwork::displayable()
            ->with(['primaryImage', 'artists', 'medias'])
            ->when(
                $filters['artist'] ?? null,
                fn($query, $artist) => $query->whereHas('artists', fn($q) => $q->where('artists.id', $artist)),
            )
            ->when(
                $filters['classification'] ?? null,
                fn($query, $classification) => $query->where('classification_id', $classification),
            )
            ->when(
                $filters['department'] ?? null,
                fn($query, $department) => $query->where('department_id', $department),
            )
            ->when($filters['medium'] ?? null, fn($query, $medium) => $query->where('medium_id', $medium))
            ->when($filters['year_from'] ?? null, fn($query, $year) => $query->where('source_begin_year', '>=', $year))
            ->when($filters['year_to'] ?? null, fn($query, $year) => $query->where('source_begin_year', '<=', $year))
            ->when(
                $filters['q'] ?? null,
                fn($query, $q) => $query->where(
                    fn($inner) => $inner->where('title', 'ilike', "%{$q}%")->orWhere('attribution', 'ilike', "%{$q}%"),
                ),
            )
            ->orderByRaw('source_begin_year nulls last')
            ->orderBy('id')
            ->paginate(24)
            ->withQueryString();

        return view('site.archive.artworks', [
            'artworks' => $artworks,
            'filters' => $filters,
            'filterOptions' => [
                'artists' => Artist::publishedInListings()
                    ->has('artworks')
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'classifications' => Classification::publishedInListings()
                    ->has('artworks')
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'departments' => Department::publishedInListings()
                    ->has('artworks')
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'mediums' => Medium::publishedInListings()
                    ->has('artworks')
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
        ]);
    }

    public function show(string $slug): Factory|View
    {
        $artwork = Artwork::displayable()
            ->forSlug($slug)
            ->with([
                'primaryImage',
                'images',
                'artists',
                'medias',
                'classification',
                'department',
                'medium',
                'sourceRecord.import',
            ])
            ->firstOrFail();

        return view('site.archive.artwork', [
            'artwork' => $artwork,
            'moreByArtist' => $this->moreByArtist($artwork),
        ]);
    }

    /**
     * Human-readable relationship: other displayable works by the same
     * artist(s), attributed plainly. No AI involved.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Artwork>
     */
    protected function moreByArtist(Artwork $artwork): mixed
    {
        $artistIds = $artwork->artists->pluck('id');

        if ($artistIds->isEmpty()) {
            return Artwork::newModelInstance()->newCollection();
        }

        return Artwork::displayable()
            ->with(['primaryImage', 'medias'])
            ->whereKeyNot($artwork->id)
            ->whereHas('artists', fn($q) => $q->whereIn('artists.id', $artistIds))
            ->orderBy('source_begin_year')
            ->take(4)
            ->get();
    }
}
