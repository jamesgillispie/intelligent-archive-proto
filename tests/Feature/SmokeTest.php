<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\Recommendation;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Mediums\Models\Medium;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;
use App\Twill\Capsules\Classifications\Models\Classification;

/**
 * The whole journey in one pass — STRATEGY §12's smallest demo plus the
 * standing rights, end-to-end on the fakes. If this test passes, the demo
 * runs. Deliberately assertion-light per step: the flow tests own the
 * detail; this owns the seams between them.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<Artwork> */
    private array $trailWorks;

    private Artwork $continuation;

    private Artwork $counterpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $cole = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);
        $painting = Classification::create(['published' => true, 'name' => 'painting', 'source_name' => 'Painting']);
        $oil = Medium::create(['published' => true, 'name' => 'oil on canvas', 'source_name' => 'oil on canvas']);
        $watercolor = Medium::create(['published' => true, 'name' => 'watercolor', 'source_name' => 'watercolor']);

        $this->trailWorks = [];

        foreach ([1830, 1834, 1838] as $year) {
            $work = Artwork::factory()
                ->withPrimaryImage()
                ->create([
                    'source_begin_year' => $year,
                    'classification_id' => $painting->id,
                    'medium_id' => $oil->id,
                ]);
            $work->artists()->attach($cole->id, ['role' => 'artist']);
            $this->trailWorks[] = $work;
        }

        $this->continuation = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'source_begin_year' => 1836,
                'classification_id' => $painting->id,
                'medium_id' => $oil->id,
            ]);
        $this->continuation->artists()->attach($cole->id, ['role' => 'artist']);

        $this->counterpoint = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'source_begin_year' => 1950,
                'classification_id' => $painting->id,
                'medium_id' => $watercolor->id,
                'medium_text' => 'watercolor',
            ]);
    }

    public function testTheSmallestDemoRunsEndToEnd(): void
    {
        // Anonymous browse — the tier that owes nothing.
        $this->get(route('archive.index'))->assertOk();
        $this->get(route('archive.artworks.show', $this->trailWorks[0]->slug))->assertOk();

        // Registration is the consent grant.
        $this->post(route('register'), [
            'name' => 'Smoke Visitor',
            'email' => 'smoke@trails.test',
            'password' => 'a-long-enough-password',
            'password_confirmation' => 'a-long-enough-password',
        ]);

        $user = User::where('email', 'smoke@trails.test')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(app(\App\Services\Memory\ConsentService::class)->hasCurrentConsent($user));

        // Build the trail: start with one work, save two more, add a note.
        $this->actingAs($user)->post(route('account.trails.store'), [
            'title' => 'Hudson Light',
            'intent_note' => 'How does Cole hold weather still?',
            'artwork_id' => $this->trailWorks[0]->id,
        ]);

        $trail = $user->trails()->sole();

        foreach ([$this->trailWorks[1], $this->trailWorks[2]] as $work) {
            $this->actingAs($user)->post(route('account.trails.items.store'), [
                'trail_id' => $trail->id,
                'artwork_id' => $work->id,
            ]);
        }

        $this->actingAs($user)->post(route('account.trails.notes.store', $trail->id), [
            'body' => 'The horizons all sit low.',
        ]);

        $this->assertSame(3, $trail->items()->count());

        // Expand this trail — the demo's center.
        $this->actingAs($user)->post(route('account.trails.expand', $trail->id));

        $shown = $user->recommendations()->get();
        $this->assertContains($this->continuation->id, $shown->pluck('artwork_id')->all());
        $this->assertContains($this->counterpoint->id, $shown->pluck('artwork_id')->all());

        $this->actingAs($user)
            ->get(route('account.trails.show', $trail->id))
            ->assertOk()
            ->assertSee('Why this suggestion?');

        // Accept the continuation; dismiss the counterpoint.
        $accept = $user->recommendations()->where('artwork_id', $this->continuation->id)->sole();
        $this->actingAs($user)->post(route('account.suggestions.accept', $accept->id));
        $this->assertSame(
            TrailItem::ORIGIN_SUGGESTION_ACCEPTED,
            $trail->items()->where('artwork_id', $this->continuation->id)->sole()->origin,
        );

        $dismiss = $user->recommendations()->where('artwork_id', $this->counterpoint->id)->sole();
        $this->actingAs($user)->post(route('account.suggestions.dismiss', $dismiss->id));

        // A fresh expansion honors the dismissal.
        $this->actingAs($user)->post(route('account.trails.expand', $trail->id));
        $this->assertSame(1, $user->recommendations()->where('artwork_id', $this->counterpoint->id)->count());

        // Compare two of the trail's works — a ledgered explicit act.
        $this->actingAs($user)
            ->get(
                route('archive.compare', [
                    'works' => [$this->trailWorks[0]->id, $this->trailWorks[1]->id],
                    'trail' => $trail->id,
                ]),
            )
            ->assertOk()
            ->assertSee('Grounded in');

        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::COMPARISON_REQUESTED)->count());

        // Export carries the whole record, suggestions included.
        $this->actingAs($user)->get(route('account.export'))->assertOk()->assertSee('Hudson Light');
        $download = $this->actingAs($user)->get(route('account.export.download'))->assertOk();
        $this->assertStringContainsString('suggestions', (string) $download->getContent());

        // Pause: reads stay open, writes are refused. The refresh drops the
        // relation this instance memoized across requests — a real browser
        // authenticates fresh every time.
        $this->actingAs($user)->post(route('account.memory.pause'))->assertRedirect(route('account.memory'));
        $user->refresh();
        $this->actingAs($user)
            ->get(route('account.trails.show', $trail->id))
            ->assertOk();
        $this->actingAs($user)
            ->post(route('account.trails.expand', $trail->id))
            ->assertForbidden();
        $this->actingAs($user)->post(route('account.memory.resume'));
        $user->refresh();

        // Delete everything; what remains is the receipt and an empty room.
        $this->actingAs($user)->delete(route('account.memory.destroy'))->assertRedirect(route('account.memory'));

        $this->assertSame(0, $user->trails()->count());
        $this->assertSame(0, $user->recommendations()->count());
        $this->assertSame(0, Recommendation::count());
        $this->assertSame([], app(RedisIrisMemoryContract::class)->inspect("user:{$user->id}:"));
        $this->assertSame([], app(RedisIrisMemoryContract::class)->inspect("trail:{$trail->id}:"));

        $receipt = $user->memoryEvents()->get();
        $this->assertCount(1, $receipt);
        $this->assertSame(MemoryEvent::MEMORY_DELETED, $receipt->first()->event_type);

        $this->actingAs($user)->get(route('account.memory'))->assertOk()->assertSee('Deleted');
    }
}
