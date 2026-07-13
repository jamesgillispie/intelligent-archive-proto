<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\View\Factory;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Twill\Capsules\CuratedSets\Models\CuratedSet;
use App\Twill\Capsules\Classifications\Models\Classification;

/**
 * Archive landing: a quiet entry point, not a dashboard.
 */
class ArchiveController extends Controller
{
    public function __invoke(): Factory|View
    {
        $recent = Artwork::displayable()
            ->with(['primaryImage', 'medias'])
            ->orderByDesc('last_synced_at')
            ->orderBy('id')
            ->take(8)
            ->get();

        return view('site.archive.index', [
            'artworkCount' => Artwork::displayable()->count(),
            'artistCount' => Artist::publishedInListings()->count(),
            'classificationCount' => Classification::publishedInListings()->count(),
            'recent' => $recent,
            'curatedSets' => $this->curatedSets(),
        ]);
    }

    /**
     * Published curated sets, in editorial order, carrying only their
     * displayable works — a set whose works are all hidden renders nothing.
     *
     * @return \Illuminate\Support\Collection<int, CuratedSet>
     */
    protected function curatedSets(): \Illuminate\Support\Collection
    {
        return CuratedSet::publishedInListings()
            ->orderBy('position')
            ->with([
                'artworks' => fn($query) => $query->displayable()->with(['primaryImage', 'medias']),
            ])
            ->get()
            ->filter(fn(CuratedSet $set) => $set->artworks->isNotEmpty())
            ->values();
    }
}
