<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\View\Factory;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
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
        ]);
    }
}
