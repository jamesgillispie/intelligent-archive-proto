<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artworks\Models\Artwork;

class SaveController extends Controller
{
    public function __construct(private readonly ConsentService $consent) {}

    /**
     * The save picker. Artwork pages are publicly cached and identical for
     * everyone, so the affordance there is a static link to this uncached,
     * consent-gated page — where the per-user choices live.
     */
    public function __invoke(Request $request, string $slug): View
    {
        $artwork = Artwork::displayable()
            ->forSlug($slug)
            ->with(['primaryImage', 'medias', 'artists'])
            ->firstOrFail();

        $user = $request->user();

        return view('site.account.save', [
            'artwork' => $artwork,
            'trails' => $user->trails()->active()->orderByDesc('last_activity_at')->get(),
            'inTrailIds' => $user
                ->trails()
                ->whereHas('items', fn($q) => $q->where('artwork_id', $artwork->id))
                ->pluck('id')
                ->all(),
            'existingSave' => $user->artworkSaves()->where('artwork_id', $artwork->id)->first(),
            'paused' => $this->consent->isPaused($user),
        ]);
    }
}
