<?php

namespace Tests\Feature\Trails;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\ResearchTrail;
use App\Models\Memory\Recommendation;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Mediums\Models\Medium;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;
use App\Twill\Capsules\Classifications\Models\Classification;

/**
 * Flow C end-to-end on the fakes: expand persists what it shows, the
 * page explains everything from the stored rows, accepting is a visible
 * origin, and dismissing suppresses forever.
 */
class TrailExpansionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ResearchTrail $trail;

    private Artwork $continuation;

    private Artwork $counterpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        app(ConsentService::class)->grant($this->user);

        $cole = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);
        $painting = Classification::create(['published' => true, 'name' => 'painting', 'source_name' => 'Painting']);
        $oil = Medium::create(['published' => true, 'name' => 'oil on canvas', 'source_name' => 'oil on canvas']);
        $watercolor = Medium::create(['published' => true, 'name' => 'watercolor', 'source_name' => 'watercolor']);

        $this->trail = ResearchTrail::factory()->create(['user_id' => $this->user->id, 'title' => 'Hudson Light']);

        foreach ([1830, 1834, 1838] as $position => $year) {
            $work = Artwork::factory()
                ->withPrimaryImage()
                ->create([
                    'source_begin_year' => $year,
                    'classification_id' => $painting->id,
                    'medium_id' => $oil->id,
                ]);
            $work->artists()->attach($cole->id, ['role' => 'artist']);

            TrailItem::factory()->create([
                'research_trail_id' => $this->trail->id,
                'artwork_id' => $work->id,
                'position' => $position + 1,
            ]);
        }

        $this->continuation = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'The Oxbow, Again',
                'source_title' => 'The Oxbow, Again',
                'source_begin_year' => 1836,
                'classification_id' => $painting->id,
                'medium_id' => $oil->id,
            ]);
        $this->continuation->artists()->attach($cole->id, ['role' => 'artist']);

        $this->counterpoint = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'Distant Weather',
                'source_title' => 'Distant Weather',
                'source_begin_year' => 1950,
                'classification_id' => $painting->id,
                'medium_id' => $watercolor->id,
                'medium_text' => 'watercolor',
            ]);
    }

    private function expand(): void
    {
        $this->actingAs($this->user)
            ->post(route('account.trails.expand', $this->trail->id))
            ->assertRedirect(route('account.trails.show', $this->trail->id));
    }

    public function testExpandPersistsAndRendersExplainedSuggestions(): void
    {
        $this->expand();

        $shown = $this->user->recommendations()->get();

        $this->assertGreaterThanOrEqual(2, $shown->count());

        foreach ($shown as $recommendation) {
            $this->assertSame(Recommendation::STATUS_SHOWN, $recommendation->status);
            $this->assertNotEmpty($recommendation->citations);
            $this->assertSame('Hudson Light', $recommendation->context['trail_title']);
            $this->assertContains('Thomas Cole', $recommendation->context['anchors']['artists']);
        }

        $this->assertContains($this->continuation->id, $shown->pluck('artwork_id')->all());
        $this->assertContains($this->counterpoint->id, $shown->pluck('artwork_id')->all());

        $this->actingAs($this->user)
            ->get(route('account.trails.show', $this->trail->id))
            ->assertOk()
            ->assertSee('Suggestions')
            ->assertSee('Continuations')
            ->assertSee('Counterpoints')
            ->assertSee('The Oxbow, Again')
            ->assertSee('Distant Weather')
            ->assertSee('Why this suggestion?')
            ->assertSee('Dismiss');
    }

    public function testExpansionRequiresEnoughWorks(): void
    {
        $small = ResearchTrail::factory()->create(['user_id' => $this->user->id]);
        TrailItem::factory()->create([
            'research_trail_id' => $small->id,
            'artwork_id' => $this->continuation->id,
            'position' => 1,
        ]);

        $this->actingAs($this->user)
            ->from(route('account.trails.show', $small->id))
            ->post(route('account.trails.expand', $small->id))
            ->assertSessionHasErrors('trail');

        $this->assertSame(0, $this->user->recommendations()->count());
    }

    public function testAcceptingASuggestionAddsToTheTrailWithVisibleOrigin(): void
    {
        $this->expand();

        $recommendation = $this->user->recommendations()->where('artwork_id', $this->continuation->id)->sole();

        $this->actingAs($this->user)
            ->post(route('account.suggestions.accept', $recommendation->id))
            ->assertRedirect(route('account.trails.show', $this->trail->id));

        $item = $this->trail->items()->where('artwork_id', $this->continuation->id)->sole();
        $this->assertSame(TrailItem::ORIGIN_SUGGESTION_ACCEPTED, $item->origin);

        $this->assertSame(Recommendation::STATUS_ACCEPTED, $recommendation->fresh()->status);
        $this->assertNotNull($recommendation->fresh()->responded_at);

        // One act, one ledger row — the save event carries the story.
        $this->assertSame(
            1,
            $this->user
                ->memoryEvents()
                ->where('event_type', MemoryEvent::ARTWORK_SAVED_TO_TRAIL)
                ->where('payload->artwork_title', 'The Oxbow, Again')
                ->count(),
        );

        $this->actingAs($this->user)
            ->get(route('account.trails.show', $this->trail->id))
            ->assertSee('From a suggestion you accepted');
    }

    public function testDismissingSuppressesTheWorkEverywhere(): void
    {
        $this->expand();

        $recommendation = $this->user->recommendations()->where('artwork_id', $this->counterpoint->id)->sole();

        $this->actingAs($this->user)
            ->post(route('account.suggestions.dismiss', $recommendation->id))
            ->assertRedirect(route('account.trails.show', $this->trail->id));

        $this->assertSame(Recommendation::STATUS_DISMISSED, $recommendation->fresh()->status);

        $event = $this->user->memoryEvents()->where('event_type', MemoryEvent::SUGGESTION_DISMISSED)->sole();
        $this->assertSame('Distant Weather', $event->payload['artwork_title']);
        $this->assertStringContainsString('Dismissed the suggestion', $event->description());

        // Suppression is held as inspectable context, immediately.
        $held = app(RedisIrisMemoryContract::class)->get("user:{$this->user->id}:recommendation_context");
        $this->assertNotNull($held);
        $this->assertContains($this->counterpoint->id, $held['dismissed_artwork_ids']);

        // A fresh expansion never re-suggests it.
        $this->actingAs($this->user)->post(route('account.trails.expand', $this->trail->id));

        $this->assertSame(1, $this->user->recommendations()->where('artwork_id', $this->counterpoint->id)->count());
    }

    public function testPausedMemoryBlocksExpansion(): void
    {
        app(ConsentService::class)->pause($this->user);

        $this->actingAs($this->user)
            ->post(route('account.trails.expand', $this->trail->id))
            ->assertForbidden();

        $this->assertSame(0, $this->user->recommendations()->count());
    }

    public function testSuggestionsAreOwnerScoped(): void
    {
        $this->expand();
        $recommendation = $this->user->recommendations()->first();

        $other = User::factory()->create();
        app(ConsentService::class)->grant($other);

        $this->actingAs($other)
            ->post(route('account.trails.expand', $this->trail->id))
            ->assertNotFound();

        $this->actingAs($other)
            ->post(route('account.suggestions.accept', $recommendation->id))
            ->assertNotFound();

        $this->actingAs($other)
            ->post(route('account.suggestions.dismiss', $recommendation->id))
            ->assertNotFound();
    }
}
