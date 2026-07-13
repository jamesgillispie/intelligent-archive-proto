<?php

namespace Tests\Feature\Archive;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\ResearchTrail;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Mediums\Models\Medium;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Twill\Capsules\Classifications\Models\Classification;

/**
 * Flow D: consent-gated, grounded comparison of the user's own works.
 * The request is itself an explicit, ledgered signal — deduped, and
 * skipped while paused.
 */
class CompareTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private ResearchTrail $trail;

    private Artwork $early;

    private Artwork $late;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        app(ConsentService::class)->grant($this->user);

        $painting = Classification::create(['published' => true, 'name' => 'painting', 'source_name' => 'Painting']);
        $oil = Medium::create(['published' => true, 'name' => 'oil on canvas', 'source_name' => 'oil on canvas']);

        $cole = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);
        $bierstadt = Artist::factory()->create(['name' => 'Albert Bierstadt', 'source_name' => 'Albert Bierstadt']);

        $this->early = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'Storm at Dawn',
                'source_title' => 'Storm at Dawn',
                'source_begin_year' => 1830,
                'classification_id' => $painting->id,
                'medium_id' => $oil->id,
            ]);
        $this->early->artists()->attach($cole->id, ['role' => 'artist']);

        $this->late = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'Valley at Dusk',
                'source_title' => 'Valley at Dusk',
                'source_begin_year' => 1920,
                'classification_id' => $painting->id,
                'medium_id' => $oil->id,
            ]);
        $this->late->artists()->attach($bierstadt->id, ['role' => 'artist']);

        $this->trail = ResearchTrail::factory()->create(['user_id' => $this->user->id, 'title' => 'Weather']);

        foreach ([$this->early, $this->late] as $position => $artwork) {
            TrailItem::factory()->create([
                'research_trail_id' => $this->trail->id,
                'artwork_id' => $artwork->id,
                'position' => $position + 1,
            ]);
        }
    }

    private function compareUrl(): string
    {
        return route('archive.compare', [
            'works' => [$this->early->id, $this->late->id],
            'trail' => $this->trail->id,
        ]);
    }

    public function testComparisonRendersGroundedClaimsBothWays(): void
    {
        $this->actingAs($this->user)
            ->get($this->compareUrl())
            ->assertOk()
            ->assertSee('Storm at Dawn')
            ->assertSee('Valley at Dusk')
            ->assertSee('Shared ground')
            ->assertSee('Both are classified as painting')
            ->assertSee('Both are oil on canvas')
            ->assertSee('Where they differ')
            ->assertSee('Made about 90 years apart')
            ->assertSee('By different hands')
            ->assertSee('Grounded in')
            ->assertSee('never from your activity');
    }

    public function testTheRequestIsLedgeredOnceWithinTheWindow(): void
    {
        $this->actingAs($this->user)->get($this->compareUrl())->assertOk();
        $this->actingAs($this->user)->get($this->compareUrl())->assertOk();

        $event = $this->user->memoryEvents()->where('event_type', MemoryEvent::COMPARISON_REQUESTED)->sole();

        $this->assertSame(['Storm at Dawn', 'Valley at Dusk'], $event->payload['artwork_titles']);
        $this->assertSame('Asked how “Storm at Dawn” and “Valley at Dusk” compare', $event->description());
    }

    public function testGuestsAreSentToLogin(): void
    {
        $this->get($this->compareUrl())->assertRedirect(route('login'));
    }

    public function testWorksOutsideTheUsersSavesAndTrailsAreRejected(): void
    {
        $foreign = Artwork::factory()->withPrimaryImage()->create();

        $this->actingAs($this->user)
            ->from(route('account.trails.show', $this->trail->id))
            ->get(route('archive.compare', ['works' => [$this->early->id, $foreign->id]]))
            ->assertRedirect(route('account.trails.show', $this->trail->id))
            ->assertSessionHasErrors('works');
    }

    public function testPausedMemoryStillReadsButWritesNothing(): void
    {
        app(ConsentService::class)->pause($this->user);

        $this->actingAs($this->user)->get($this->compareUrl())->assertOk()->assertSee('Shared ground');

        $this->assertSame(
            0,
            $this->user->memoryEvents()->where('event_type', MemoryEvent::COMPARISON_REQUESTED)->count(),
        );
    }
}
