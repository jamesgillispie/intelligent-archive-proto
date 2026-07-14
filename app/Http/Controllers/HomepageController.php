<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use App\Services\Memory\ConsentService;
use App\Services\Trails\TrailSeedService;
use App\Http\Controllers\Traits\SetsMetadata;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Memory\AnonymousContextService;
use App\Twill\Capsules\Homepages\Models\Homepage;
use App\Http\Controllers\Traits\SuggestsStartingPoints;
use App\Twill\Capsules\Homepages\Repositories\HomepageRepository;

/**
 * The front door is a search bar. What surrounds it depends on who is
 * standing there: everyone gets the archive's own starting points;
 * anonymous visitors also get their session's earlier queries; consented
 * visitors get seeds drawn from their trails and a way back into them.
 * Rendering the home page only ever reads — it never writes context
 * anywhere, so the page itself stays inside the consent model.
 */
class HomepageController extends Controller
{
    use SetsMetadata;
    use SuggestsStartingPoints;

    public function __construct(
        protected ConsentService $consent,
        protected TrailSeedService $seeds,
        protected AnonymousContextService $anonymous,
    ) {}

    public function __invoke(Request $request): Factory|View
    {
        $landing = Homepage::query()->first();

        if ($landing !== null) {
            $this->setMetadata($landing);
        }

        $user = $request->user();
        $consented = $user !== null && $this->consent->hasCurrentConsent($user);

        return view('site.homepage', [
            'hero' =>
                $landing === null
                    ? ['tagline' => null, 'image' => null]
                    : app(HomepageRepository::class)->getShowData($landing)['hero'],
            'artworkCount' => Artwork::displayable()->count(),
            'earlierQueries' =>
                $user === null ? array_slice($this->anonymous->recentQueries($request->session()->getId()), 0, 4) : [],
            'trailSeeds' => $this->seeds->seedsFor($user),
            'recentTrails' => $consented
                ? $user->trails()->active()->orderByDesc('last_activity_at')->orderByDesc('id')->take(3)->get()
                : collect(),
            'startingPoints' => $this->startingPoints(),
        ]);
    }
}
