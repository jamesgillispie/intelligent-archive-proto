<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Factory;
use App\Services\Memory\VisitorContextService;
use App\Services\Nexus\Artifacts\ExploreResult;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Contracts\NexusRetriever;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Limited, non-personalized semantic exploration — the anonymous tier of
 * the consent model (CLAUDE.md). Every visitor gets the same results for
 * the same query; the route is rate-limited per session, and the only
 * thing held is a session-scoped list of recent queries in Iris under
 * `session:{id}:` — TTL-capped, never linked to an account.
 */
class ExploreController extends Controller
{
    public function __construct(
        protected NexusRetriever $retriever,
        protected RedisIrisMemoryContract $iris,
        protected VisitorContextService $visitors,
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
            'earlierQueries' => $this->rememberSessionQuery($request, $query),
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
            ->with(['primaryImage', 'medias'])
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
     * Holds the last few queries for this session only, and returns the
     * earlier ones (excluding the current query) for a quiet "earlier this
     * session" line.
     *
     * @return list<string>
     */
    protected function rememberSessionQuery(Request $request, string $query): array
    {
        if ($request->user() !== null) {
            // Signed-in explore stays non-personalized too: no context is
            // written anywhere until the consented user-context surface
            // exists (Phase 7). Session context would silently link a
            // session to an account — exactly the line Iris must not cross.
            return [];
        }

        $namespace = $this->visitors->sessionNamespace($request->session()->getId(), 'anonymous_context');
        $held = $this->iris->get($namespace);

        $earlier = array_values(
            array_filter(
                (array) ($held['recent_queries'] ?? []),
                fn($previous) => is_string($previous) && $previous !== '' && $previous !== $query,
            ),
        );

        if ($query !== '') {
            $this->iris->put(
                $namespace,
                ['recent_queries' => array_slice([$query, ...$earlier], 0, 5)],
                (int) config('memory.anonymous_context_ttl_minutes') * 60,
            );
        }

        return array_slice($earlier, 0, 4);
    }
}
