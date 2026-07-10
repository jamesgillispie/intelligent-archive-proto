<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\View\Factory;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;

class ArtistsController extends Controller
{
    public function index(): Factory|View
    {
        $filters = $this->validate(request(), [
            'q' => ['nullable', 'string', 'max:200'],
        ]);

        $artists = Artist::publishedInListings()
            ->has('artworks')
            ->withCount('artworks')
            ->when($filters['q'] ?? null, fn($query, $q) => $query->where('name', 'ilike', "%{$q}%"))
            ->orderBy('name')
            ->paginate(48)
            ->withQueryString();

        return view('site.archive.artists', [
            'artists' => $artists,
            'filters' => $filters,
        ]);
    }

    public function show(string $slug): Factory|View
    {
        $artist = Artist::publishedInListings()->forSlug($slug)->firstOrFail();

        $artworks = Artwork::displayable()
            ->with(['primaryImage', 'medias'])
            ->whereHas('artists', fn($q) => $q->where('artists.id', $artist->id))
            ->orderByRaw('source_begin_year nulls last')
            ->paginate(24);

        return view('site.archive.artist', [
            'artist' => $artist,
            'artworks' => $artworks,
        ]);
    }
}
