<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Models\Memory\ResearchTrail;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;
use App\Twill\Capsules\Classifications\Models\Classification;
use App\Twill\Capsules\Homepages\Repositories\HomepageRepository;

/**
 * The front door is a search bar, and what surrounds it follows the
 * consent model: the archive's own starting points for everyone, session
 * queries for guests, trail seeds only for consented users — and the page
 * itself never writes context and is never publicly cached.
 */
class HomepageTest extends TestCase
{
    use RefreshDatabase;

    private function iris(): RedisIrisMemoryContract
    {
        return app(RedisIrisMemoryContract::class);
    }

    private function seedCollection(): Artwork
    {
        $classification = Classification::create([
            'published' => true,
            'name' => 'Painting',
            'source_name' => 'Painting',
        ]);

        $artist = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);

        $artwork = Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'Storm at Sea', 'source_title' => 'Storm at Sea']);
        $artwork->update(['classification_id' => $classification->id]);
        $artwork->artists()->attach($artist->id, ['role' => 'artist']);

        return $artwork;
    }

    public function testTheFrontDoorIsASearchBar(): void
    {
        $this->seedCollection();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('What are you curious about?')
            ->assertSee(route('archive.explore'), false)
            ->assertSee('Trails, not feeds.');
    }

    public function testHomeIsNeverPubliclyCached(): void
    {
        $cacheControl = (string) $this->get(route('home'))->assertOk()->headers->get('Cache-Control');

        $this->assertStringNotContainsString('public', $cacheControl);
        $this->assertStringNotContainsString('s-maxage', $cacheControl);
    }

    public function testEveryoneGetsStartingPointsFromTheCollectionItself(): void
    {
        $this->seedCollection();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('start from the collection itself')
            ->assertSee('Painting')
            ->assertSee('Thomas Cole');
    }

    public function testRenderingHomeWritesNoContextAnywhere(): void
    {
        $this->seedCollection();

        $this->get(route('home'))->assertOk();

        $this->assertSame([], $this->iris()->inspect('session:'));
        $this->assertSame([], $this->iris()->inspect('user:'));
    }

    public function testGuestsSeeTheirEarlierSessionQueriesOnTheFrontDoor(): void
    {
        $this->seedCollection();

        $first = $this->get(route('archive.explore', ['q' => 'storms']));

        // Carry the session cookie the way a browser would.
        $cookie = collect($first->headers->getCookies())->first(
            fn($candidate) => $candidate->getName() === config('session.cookie'),
        );
        $this->assertNotNull($cookie);

        $this->withUnencryptedCookie(config('session.cookie'), (string) $cookie->getValue())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Earlier this session')
            ->assertSee('storms');
    }

    public function testConsentedVisitorsGetTrailSeedsAndAWayBackIn(): void
    {
        $artwork = $this->seedCollection();

        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        $trail = ResearchTrail::factory()->create(['user_id' => $user->id, 'title' => 'Weather at sea']);
        TrailItem::factory()->create([
            'research_trail_id' => $trail->id,
            'artwork_id' => $artwork->id,
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('From your trails')
            ->assertSee('Thomas Cole')
            ->assertSee('Pick up where you left off')
            ->assertSee('Weather at sea')
            ->assertSee(route('account.trails.show', $trail), false);

        // Seeds are read from the user's own record; nothing is written.
        $this->assertSame([], $this->iris()->inspect("user:{$user->id}:"));
        $this->assertSame([], $this->iris()->inspect('session:'));
    }

    public function testSignedInVisitorsWithoutConsentGetTheGenericTier(): void
    {
        $artwork = $this->seedCollection();

        $user = User::factory()->create();

        $trail = ResearchTrail::factory()->create(['user_id' => $user->id, 'title' => 'Weather at sea']);
        TrailItem::factory()->create([
            'research_trail_id' => $trail->id,
            'artwork_id' => $artwork->id,
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('What are you curious about?')
            ->assertDontSee('From your trails')
            ->assertDontSee('Pick up where you left off')
            ->assertDontSee('Weather at sea');
    }

    public function testTheEditorialTaglineLeadsWhenOneExists(): void
    {
        app(HomepageRepository::class)->create([
            'title' => 'Homepage',
            'published' => true,
            'tagline' => 'A slower archive.',
        ]);

        $this->get(route('home'))->assertOk()->assertSee('A slower archive.')->assertDontSee('Trails, not feeds.');
    }
}
