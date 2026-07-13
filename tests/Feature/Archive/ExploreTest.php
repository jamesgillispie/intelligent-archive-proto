<?php

namespace Tests\Feature\Archive;

use Tests\TestCase;
use App\Models\User;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Artifacts\RelatedArtwork;
use App\Services\Nexus\Contracts\NexusRetriever;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * The anonymous tier of the consent model: limited, non-personalized
 * semantic exploration — rate-limited per session, with the only held
 * state a session-scoped, TTL-capped list of recent queries.
 */
class ExploreTest extends TestCase
{
    use RefreshDatabase;

    private function iris(): RedisIrisMemoryContract
    {
        return app(RedisIrisMemoryContract::class);
    }

    private function seedCollection(): void
    {
        Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'Storm at Sea', 'source_title' => 'Storm at Sea']);
        Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'Quiet Interior', 'source_title' => 'Quiet Interior']);
    }

    public function testAnonymousVisitorsGetCitedExplainedMatches(): void
    {
        $this->seedCollection();

        $this->get(route('archive.explore', ['q' => 'storm']))
            ->assertOk()
            ->assertSee('Storm at Sea')
            ->assertSee('The title mentions')
            ->assertSee('Why this?')
            ->assertDontSee('Quiet Interior');
    }

    public function testExploreIsDeterministicAndEveryMatchIsCited(): void
    {
        $this->seedCollection();

        $first = app(NexusRetriever::class)->explore('storm at sea');
        $again = app(NexusRetriever::class)->explore('storm at sea');

        $this->assertFalse($first->isEmpty());

        foreach ($first->matches as $match) {
            $this->assertSame(RelatedArtwork::KIND_QUERY_MATCH, $match->kind);
            $this->assertGreaterThan(0, count($match->citations));
        }

        $this->assertEquals(
            array_map(fn(RelatedArtwork $m) => [$m->artworkId, $m->score], $first->matches),
            array_map(fn(RelatedArtwork $m) => [$m->artworkId, $m->score], $again->matches),
        );
    }

    public function testAnEmptyQueryShowsThePromptWithoutResults(): void
    {
        $this->get(route('archive.explore'))
            ->assertOk()
            ->assertSee('Explore by idea')
            ->assertSee('same for every visitor')
            ->assertDontSee('Nothing surfaced');
    }

    public function testExploreIsRateLimitedPerVisitor(): void
    {
        config(['memory.anonymous_explore_rate_limit' => 2]);
        $this->seedCollection();

        // A signed-in visitor keys the limiter stably (guests key by
        // session id, which a browser holds via its cookie).
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('archive.explore', ['q' => 'storm']))
            ->assertOk();
        $this->actingAs($user)
            ->get(route('archive.explore', ['q' => 'sea']))
            ->assertOk();
        $this->actingAs($user)
            ->get(route('archive.explore', ['q' => 'interior']))
            ->assertStatus(429);
    }

    public function testGuestQueriesAreHeldSessionScopedAndTtlCapped(): void
    {
        $this->seedCollection();

        $this->get(route('archive.explore', ['q' => 'storms']))->assertOk();

        $held = $this->iris()->inspect('session:');

        $this->assertCount(1, $held);
        $namespace = array_key_first($held);
        $this->assertStringEndsWith(':anonymous_context', (string) $namespace);
        $this->assertSame(['storms'], $held[$namespace]['recent_queries']);

        // Strictly isolated from every user prefix.
        $this->assertSame([], $this->iris()->inspect('user:'));

        // The hard TTL cap: session context must never accumulate.
        $this->travel((int) config('memory.anonymous_context_ttl_minutes') + 1)->minutes();
        $this->assertSame([], $this->iris()->inspect('session:'));
    }

    public function testASecondRequestInTheSameSessionShowsEarlierQueries(): void
    {
        $this->seedCollection();

        $first = $this->get(route('archive.explore', ['q' => 'storms']));

        // Carry the session cookie the way a browser would.
        $cookie = collect($first->headers->getCookies())->first(
            fn($candidate) => $candidate->getName() === config('session.cookie'),
        );
        $this->assertNotNull($cookie);

        $this->withUnencryptedCookie(config('session.cookie'), (string) $cookie->getValue())
            ->get(route('archive.explore', ['q' => 'interiors']))
            ->assertOk()
            ->assertSee('Earlier this session')
            ->assertSee('storms');
    }

    public function testSignedInExploreWritesNoContextAnywhere(): void
    {
        $this->seedCollection();

        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        $this->actingAs($user)
            ->get(route('archive.explore', ['q' => 'storm']))
            ->assertOk();

        // No session context (it would link a session to an account) and
        // no user context — queries are not an enumerated explicit action
        // and are never stored durably.
        $this->assertSame([], $this->iris()->inspect('session:'));
        $this->assertSame([], $this->iris()->inspect("user:{$user->id}:"));
    }

    public function testConsentedVisitorsGetTrailDrawnSeedsWithoutAnyWrites(): void
    {
        $this->seedCollection();

        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        $artist = \App\Twill\Capsules\Artists\Models\Artist::factory()->create([
            'name' => 'Thomas Cole',
            'source_name' => 'Thomas Cole',
        ]);
        $saved = Artwork::factory()->withPrimaryImage()->create();
        $saved->artists()->attach($artist->id, ['role' => 'artist']);

        $trail = \App\Models\Memory\ResearchTrail::factory()->create(['user_id' => $user->id]);
        \App\Models\Memory\TrailItem::factory()->create([
            'research_trail_id' => $trail->id,
            'artwork_id' => $saved->id,
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('archive.explore'))
            ->assertOk()
            ->assertSee('From your trails')
            ->assertSee('Thomas Cole');

        // Seeds are read from the user's own record; nothing is written.
        $this->assertSame([], $this->iris()->inspect("user:{$user->id}:"));
        $this->assertSame([], $this->iris()->inspect('session:'));
    }

    public function testGuestsSeeNoTrailSeeds(): void
    {
        $this->seedCollection();

        $this->get(route('archive.explore'))->assertOk()->assertDontSee('From your trails');
    }
}
