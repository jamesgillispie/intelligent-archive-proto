<?php

namespace Tests\Feature\Trails;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\MemoryConsent;
use App\Models\Memory\ResearchTrail;
use App\Services\Memory\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrailsTest extends TestCase
{
    use RefreshDatabase;

    private function consentedUser(): User
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        return $user;
    }

    public function testGuestsAreSentToLogin(): void
    {
        $this->get(route('account.trails'))->assertRedirect(route('login'));
    }

    public function testTrailsIndexListsTrailsByRecency(): void
    {
        $user = $this->consentedUser();
        ResearchTrail::factory()->create([
            'user_id' => $user->id,
            'title' => 'Dutch Interiors',
            'last_activity_at' => now()->subDay(),
        ]);
        ResearchTrail::factory()->create([
            'user_id' => $user->id,
            'title' => 'Hudson River Light',
            'last_activity_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('account.trails'))
            ->assertOk()
            ->assertSee('Continue where you left off')
            ->assertSee('Hudson River Light')
            ->assertSee('Dutch Interiors');
    }

    public function testStartingATrailRecordsTheLedgerEvent(): void
    {
        $user = $this->consentedUser();

        $response = $this->actingAs($user)->post(route('account.trails.store'), [
            'title' => 'Gilded Age Portraits',
            'intent_note' => 'Who paid for these faces?',
        ]);

        $trail = $user->trails()->sole();
        $response->assertRedirect(route('account.trails.show', $trail->id));

        $this->assertSame('Gilded Age Portraits', $trail->title);
        $this->assertSame('Who paid for these faces?', $trail->intent_note);
        $this->assertSame(ResearchTrail::STATUS_ACTIVE, $trail->status);

        $event = $user->memoryEvents()->where('event_type', MemoryEvent::TRAIL_STARTED)->sole();
        $this->assertSame('Gilded Age Portraits', $event->payload['trail_title']);
    }

    public function testTrailLimitIsEnforced(): void
    {
        config()->set('trails.max_per_user', 1);
        $user = $this->consentedUser();
        ResearchTrail::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from(route('account.trails'))
            ->post(route('account.trails.store'), ['title' => 'One Too Many'])
            ->assertRedirect(route('account.trails'))
            ->assertSessionHasErrors('title');

        $this->assertSame(1, $user->trails()->count());
    }

    public function testOwnersSeeTheirWorkspaceOthersGetNotFound(): void
    {
        $owner = $this->consentedUser();
        $stranger = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $owner->id, 'title' => 'Dutch Interiors']);

        $this->actingAs($owner)
            ->get(route('account.trails.show', $trail->id))
            ->assertOk()
            ->assertSee('Dutch Interiors');

        $this->actingAs($stranger)
            ->get(route('account.trails.show', $trail->id))
            ->assertNotFound();
    }

    public function testDeliberateReturnsAreLedgeredOnceWithinTheWindow(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create([
            'user_id' => $user->id,
            'last_activity_at' => now()->subHours(3),
        ]);

        $this->actingAs($user)
            ->get(route('account.trails.show', $trail->id))
            ->assertOk();
        $this->actingAs($user)
            ->get(route('account.trails.show', $trail->id))
            ->assertOk();

        $this->assertSame(
            1,
            $user->memoryEvents()->where('event_type', MemoryEvent::TRAIL_REVISITED)->count(),
            'A refresh is not a second visit',
        );
    }

    public function testRevisitsAreNotRecordedWhilePaused(): void
    {
        $user = $this->consentedUser();
        $user->personalizationSetting->update(['memory_paused' => true]);
        $trail = ResearchTrail::factory()->create([
            'user_id' => $user->id,
            'last_activity_at' => now()->subHours(3),
        ]);

        $this->actingAs($user)
            ->get(route('account.trails.show', $trail->id))
            ->assertOk();

        $this->assertSame(0, $user->memoryEvents()->where('event_type', MemoryEvent::TRAIL_REVISITED)->count());
    }

    public function testArchiveAndReactivateRoundTrip(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('account.trails.archive', $trail->id))
            ->assertRedirect(route('account.trails'));
        $this->assertTrue($trail->fresh()->isArchived());

        $this->actingAs($user)
            ->post(route('account.trails.reactivate', $trail->id))
            ->assertRedirect(route('account.trails'));
        $this->assertFalse($trail->fresh()->isArchived());

        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::TRAIL_ARCHIVED)->count());
        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::TRAIL_REACTIVATED)->count());
    }

    public function testDeletingATrailRemovesItemsAndNotesButLeavesAReceipt(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id, 'title' => 'Dutch Interiors']);
        $item = TrailItem::factory()->create(['research_trail_id' => $trail->id]);
        $trail->notes()->create(['user_id' => $user->id, 'body' => 'A note.']);

        $this->actingAs($user)
            ->delete(route('account.trails.destroy', $trail->id))
            ->assertRedirect(route('account.memory'));

        $this->assertDatabaseMissing('research_trails', ['id' => $trail->id]);
        $this->assertDatabaseMissing('trail_items', ['id' => $item->id]);
        $this->assertSame(0, $user->notes()->count());

        $receipt = $user->memoryEvents()->where('event_type', MemoryEvent::TRAIL_DELETED)->sole();
        $this->assertSame('Dutch Interiors', $receipt->payload['trail_title']);
        $this->assertSame(1, $receipt->payload['item_count']);
    }

    public function testDeletionWorksWhilePausedAndWithStaleConsent(): void
    {
        // Paused memory blocks writes, never the right to delete.
        $paused = $this->consentedUser();
        $paused->personalizationSetting->update(['memory_paused' => true]);
        $pausedTrail = ResearchTrail::factory()->create(['user_id' => $paused->id]);

        $this->actingAs($paused)
            ->delete(route('account.trails.destroy', $pausedTrail->id))
            ->assertRedirect();
        $this->assertDatabaseMissing('research_trails', ['id' => $pausedTrail->id]);

        // Stale consent blocks new memory, never the right to delete.
        $stale = User::factory()->create();
        MemoryConsent::factory()
            ->stale()
            ->create(['user_id' => $stale->id]);
        $staleTrail = ResearchTrail::factory()->create(['user_id' => $stale->id]);

        $this->actingAs($stale)
            ->delete(route('account.trails.destroy', $staleTrail->id))
            ->assertRedirect();
        $this->assertDatabaseMissing('research_trails', ['id' => $staleTrail->id]);
    }

    public function testOthersCannotDeleteYourTrail(): void
    {
        $trail = ResearchTrail::factory()->create();

        $this->actingAs($this->consentedUser())
            ->delete(route('account.trails.destroy', $trail->id))
            ->assertNotFound();

        $this->assertDatabaseHas('research_trails', ['id' => $trail->id]);
    }
}
