<?php

namespace Tests\Feature\Trails;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\ResearchTrail;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Flow A end-to-end: the static affordance on the cached artwork page, the
 * uncached picker behind it, and the three ways a work enters memory.
 */
class SaveFlowTest extends TestCase
{
    use RefreshDatabase;

    private function consentedUser(): User
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        return $user;
    }

    public function testArtworkPagesCarryTheStaticSaveAffordance(): void
    {
        $artwork = Artwork::factory()->create();

        // The page is publicly cached, so the affordance must be identical
        // for every visitor — a plain link, no per-user state.
        $this->get(route('archive.artworks.show', $artwork->slug))
            ->assertOk()
            ->assertSee('Save to trail')
            ->assertSee(route('account.save', $artwork->slug), false);
    }

    public function testThePickerIsConsentGated(): void
    {
        $artwork = Artwork::factory()->create();

        $this->get(route('account.save', $artwork->slug))->assertRedirect(route('login'));
    }

    public function testThePickerShowsTheThreeQuietChoices(): void
    {
        $user = $this->consentedUser();
        $artwork = Artwork::factory()->create();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id, 'title' => 'Dutch Interiors']);
        TrailItem::factory()->create(['research_trail_id' => $trail->id, 'artwork_id' => $artwork->id]);
        ResearchTrail::factory()->create(['user_id' => $user->id, 'title' => 'Hudson River Light']);

        $this->actingAs($user)
            ->get(route('account.save', $artwork->slug))
            ->assertOk()
            ->assertSee('Save for later')
            ->assertSee('Save to a trail')
            ->assertSee('Start a new trail with this work')
            ->assertSee('Hudson River Light')
            ->assertSee('already in this trail');
    }

    public function testSaveForLaterIsIdempotentAndLedgered(): void
    {
        $user = $this->consentedUser();
        $artwork = Artwork::factory()->create(['title' => 'The Skater']);

        $this->actingAs($user)
            ->post(route('account.saves.store'), ['artwork_id' => $artwork->id])
            ->assertRedirect(route('account.memory'));

        $this->actingAs($user)->post(route('account.saves.store'), ['artwork_id' => $artwork->id]);

        $this->assertSame(1, $user->artworkSaves()->count());

        $event = $user->memoryEvents()->where('event_type', MemoryEvent::ARTWORK_SAVED)->sole();
        $this->assertSame('The Skater', $event->payload['artwork_title']);
    }

    public function testAddingToATrailCreatesTheItemAndTheEvent(): void
    {
        $user = $this->consentedUser();
        $artwork = Artwork::factory()->create(['title' => 'The Skater']);
        $trail = ResearchTrail::factory()->create([
            'user_id' => $user->id,
            'title' => 'Dutch Interiors',
            'last_activity_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->post(route('account.trails.items.store'), [
                'trail_id' => $trail->id,
                'artwork_id' => $artwork->id,
                'note' => 'That impossible poise.',
            ])
            ->assertRedirect(route('account.trails.show', $trail->id));

        $item = $trail->items()->sole();
        $this->assertSame($artwork->id, $item->artwork_id);
        $this->assertSame(1, $item->position);
        $this->assertSame(TrailItem::ORIGIN_USER_SAVED, $item->origin);
        $this->assertSame('That impossible poise.', $item->note);

        $event = $user->memoryEvents()->where('event_type', MemoryEvent::ARTWORK_SAVED_TO_TRAIL)->sole();
        $this->assertSame('The Skater', $event->payload['artwork_title']);
        $this->assertSame('Dutch Interiors', $event->payload['trail_title']);

        $this->assertTrue($trail->fresh()->last_activity_at->isCurrentDay());
    }

    public function testPositionsAppendAndDuplicatesStayQuiet(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);
        [$first, $second] = Artwork::factory()->count(2)->create();

        $this->actingAs($user)->post(route('account.trails.items.store'), [
            'trail_id' => $trail->id,
            'artwork_id' => $first->id,
        ]);
        $this->actingAs($user)->post(route('account.trails.items.store'), [
            'trail_id' => $trail->id,
            'artwork_id' => $second->id,
        ]);
        $this->actingAs($user)->post(route('account.trails.items.store'), [
            'trail_id' => $trail->id,
            'artwork_id' => $first->id,
        ]);

        $this->assertSame([1, 2], $trail->items()->pluck('position')->all());
        $this->assertSame(
            2,
            $user->memoryEvents()->where('event_type', MemoryEvent::ARTWORK_SAVED_TO_TRAIL)->count(),
            'Re-saving an item is idempotent — no duplicate ledger entry',
        );
    }

    public function testYouCannotAddToTrailsThatAreNotYoursOrNotActive(): void
    {
        $user = $this->consentedUser();
        $artwork = Artwork::factory()->create();

        $foreign = ResearchTrail::factory()->create();
        $archived = ResearchTrail::factory()
            ->archived()
            ->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('account.trails.items.store'), ['trail_id' => $foreign->id, 'artwork_id' => $artwork->id])
            ->assertNotFound();

        $this->actingAs($user)
            ->post(route('account.trails.items.store'), ['trail_id' => $archived->id, 'artwork_id' => $artwork->id])
            ->assertNotFound();

        $this->assertSame(0, TrailItem::count());
    }

    public function testTheItemLimitIsEnforced(): void
    {
        config()->set('trails.max_items', 1);
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);
        TrailItem::factory()->create(['research_trail_id' => $trail->id]);
        $artwork = Artwork::factory()->create();

        $this->actingAs($user)
            ->from(route('account.save', $artwork->slug))
            ->post(route('account.trails.items.store'), ['trail_id' => $trail->id, 'artwork_id' => $artwork->id])
            ->assertRedirect(route('account.save', $artwork->slug))
            ->assertSessionHasErrors('artwork_id');

        $this->assertSame(1, $trail->items()->count());
    }

    public function testStartingATrailFromThePickerSeedsTheFirstItem(): void
    {
        $user = $this->consentedUser();
        $artwork = Artwork::factory()->create(['title' => 'The Skater']);

        $this->actingAs($user)->post(route('account.trails.store'), [
            'title' => 'Poise and Ice',
            'artwork_id' => $artwork->id,
            'note' => 'Where it started.',
        ]);

        $trail = $user->trails()->sole();
        $item = $trail->items()->sole();
        $this->assertSame($artwork->id, $item->artwork_id);
        $this->assertSame('Where it started.', $item->note);

        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::TRAIL_STARTED)->count());
        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::ARTWORK_SAVED_TO_TRAIL)->count());
    }

    public function testRemovingAnItemIsLedgeredAndAllowedWhilePaused(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);
        $item = TrailItem::factory()->create(['research_trail_id' => $trail->id]);

        $user->personalizationSetting->update(['memory_paused' => true]);

        $this->actingAs($user)
            ->delete(route('account.trails.items.destroy', [$trail->id, $item->id]))
            ->assertRedirect(route('account.trails.show', $trail->id));

        $this->assertDatabaseMissing('trail_items', ['id' => $item->id]);
        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::TRAIL_ITEM_REMOVED)->count());
    }

    public function testPausedMemoryBlocksNewSaves(): void
    {
        $user = $this->consentedUser();
        $user->personalizationSetting->update(['memory_paused' => true]);
        $artwork = Artwork::factory()->create();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('account.saves.store'), ['artwork_id' => $artwork->id])
            ->assertForbidden();
        $this->actingAs($user)
            ->post(route('account.trails.items.store'), ['trail_id' => $trail->id, 'artwork_id' => $artwork->id])
            ->assertForbidden();

        $this->assertSame(0, $user->artworkSaves()->count());
        $this->assertSame(0, TrailItem::count());
    }
}
