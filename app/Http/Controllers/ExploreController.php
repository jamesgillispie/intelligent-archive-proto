<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use App\Services\Trails\TrailSeedService;
use App\Services\Nexus\Artifacts\ExploreResult;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Memory\AnonymousContextService;
use App\Services\Nexus\Contracts\NexusRetriever;
use App\Http\Controllers\Traits\SuggestsStartingPoints;

/**
 * Limited, non-personalized semantic exploration — the anonymous tier of
 * the consent model (CLAUDE.md). Every visitor gets the same results for
 * the same query; the route is rate-limited per session, and the only
 * thing held is a session-scoped list of recent queries in Iris under
 * `session:{id}:` — TTL-capped, never linked to an account.
 */
class ExploreController extends Controller
{
    use SuggestsStartingPoints;

    public function __construct(
        protected NexusRetriever $retriever,
        protected AnonymousContextService $anonymous,
        protected TrailSeedService $seeds,
    ) {}

    public function __invoke(Request $request): Factory|View
    {
        $validated = $this->validate($request, ['q' => ['nullable', 'string', 'max:200']]);
        $query = trim((string) ($validated['q'] ?? ''));

        $result = $query === '' ? null : $this->retriever->explore($query);

        return view('site.archive.explore', [
            'query' => $query,
            'result' => $result,
            'matches' => $result === null ? [] : $this->hydrate($result),
            'earlierQueries' => $this->earlierQueries($request, $query),
            // Signed-in, currently consented visitors get starting points
            // drawn from their own trails. Only the seeds are personal:
            // results for any query stay identical for every visitor, and
            // free-text queries are not an enumerated explicit action, so
            // they are never stored durably.
            'trailSeeds' => $this->seeds->seedsFor($request->user(), $query),
            // A query that surfaces nothing still leaves the visitor
            // somewhere to go — the archive's own starting points.
            'startingPoints' => $result !== null && $result->isEmpty() ? $this->startingPoints() : [],
        ]);
    }

    /**
     * @return list<array{artwork: Artwork, match: \App\Services\Nexus\Artifacts\RelatedArtwork}>
     */
    protected function hydrate(ExploreResult $result): array
    {
        if ($result->isEmpty()) {
            return [];
        }

        $artworks = Artwork::displayable()
            ->with(['primaryImage', 'medias', 'slugs'])
            ->findMany($result->artworkIds())
            ->keyBy('id');

        $matches = [];

        foreach ($result as $match) {
            $model = $artworks->get($match->artworkId);

            if ($model === null) {
                continue;
            }

            $matches[] = ['artwork' => $model, 'match' => $match];
        }

        return $matches;
    }

    /**
     * Holds the current query for this session only and returns the
     * earlier ones for a quiet "earlier this session" line.
     *
     * @return list<string>
     */
    protected function earlierQueries(Request $request, string $query): array
    {
        if ($request->user() !== null) {
            // Signed-in explore stays non-personalized too: no context is
            // written anywhere. Session context would silently link a
            // session to an account — exactly the line Iris must not cross.
            return [];
        }

        $sessionId = $request->session()->getId();
        $earlier = $this->anonymous->recentQueries($sessionId, $query);

        if ($query !== '') {
            $this->anonymous->rememberQuery($sessionId, $query);
        }

        return array_slice($earlier, 0, 4);
    }
}
