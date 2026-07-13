<?php

namespace Tests\Feature\Memory;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\ArtworkSave;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\MemoryConsent;
use App\Models\Memory\ResearchTrail;
use App\Services\Memory\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemoryDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function consentedUser(): User
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        return $user;
    }

    public function testTheDashboardShowsEverythingHeld(): void
    {
        $user = $this->consentedUser();
        $save = ArtworkSave::factory()->create(['user_id' => $user->id]);
        ResearchTrail::factory()->create(['user_id' => $user->id]);
        MemoryEvent::factory()->create([
            'user_id' => $user->id,
            'payload' => ['artwork_title' => 'The Skater'],
        ]);

        $this->actingAs($user)
            ->get(route('account.memory'))
            ->assertOk()
            ->assertSee('Your memory')
            ->assertSee(config('memory.consent_version'))
            ->assertSee('Memory is on')
            ->assertSee($save->artwork->title)
            ->assertSee('1 trail')
            ->assertSee('Saved “The Skater” for later');
    }

    public function testTheDashboardIsNeverPubliclyCacheable(): void
    {
        $response = $this->actingAs($this->consentedUser())->get(route('account.memory'));

        $this->assertStringNotContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function testPauseAndResumeAreLedgeredSwitches(): void
    {
        $user = $this->consentedUser();

        $this->actingAs($user)->post(route('account.memory.pause'))->assertRedirect(route('account.memory'));
        $this->assertTrue((bool) $user->personalizationSetting->fresh()->memory_paused);

        // Resume must work while paused — it sits outside the consented gate.
        $this->actingAs($user)->post(route('account.memory.resume'))->assertRedirect(route('account.memory'));
        $this->assertFalse((bool) $user->personalizationSetting->fresh()->memory_paused);

        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::MEMORY_PAUSED)->count());
        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::MEMORY_RESUMED)->count());
    }

    public function testInspectionAndDeletionSurviveStaleConsentAndPause(): void
    {
        $user = User::factory()->create();
        MemoryConsent::factory()
            ->stale()
            ->create(['user_id' => $user->id]);
        $save = ArtworkSave::factory()->create(['user_id' => $user->id]);
        $event = MemoryEvent::factory()->create(['user_id' => $user->id]);

        // Stale consent: the working surfaces redirect to renewal…
        $this->actingAs($user)->get(route('account.trails'))->assertRedirect(route('account.consent'));

        // …but the dashboard stays open, and deletion stays possible.
        $this->actingAs($user)->get(route('account.memory'))->assertOk()->assertSee('review and renew it');
        $this->actingAs($user)
            ->delete(route('account.saves.destroy', $save->id))
            ->assertRedirect();
        $this->actingAs($user)
            ->delete(route('account.memory.events.destroy', $event->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('artwork_saves', ['id' => $save->id]);
        $this->assertDatabaseMissing('memory_events', ['id' => $event->id]);
    }

    public function testRemovingASaveLeavesALedgerReceipt(): void
    {
        $user = $this->consentedUser();
        $save = ArtworkSave::factory()->create(['user_id' => $user->id]);
        $title = $save->artwork->title;

        $this->actingAs($user)
            ->delete(route('account.saves.destroy', $save->id))
            ->assertRedirect(route('account.memory'));

        $this->assertDatabaseMissing('artwork_saves', ['id' => $save->id]);

        $receipt = $user->memoryEvents()->where('event_type', MemoryEvent::SAVE_REMOVED)->sole();
        $this->assertSame($title, $receipt->payload['artwork_title']);
    }

    public function testDeletingALedgerEntryIsNotItselfLedgered(): void
    {
        $user = $this->consentedUser();
        $event = MemoryEvent::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('account.memory.events.destroy', $event->id))
            ->assertRedirect();

        $this->assertSame(0, $user->memoryEvents()->count(), 'The ledger never writes about itself');
    }

    public function testOthersMemoryIsInvisibleAndUndeletable(): void
    {
        $save = ArtworkSave::factory()->create();
        $event = MemoryEvent::factory()->create();

        $stranger = $this->consentedUser();

        $this->actingAs($stranger)
            ->delete(route('account.saves.destroy', $save->id))
            ->assertNotFound();
        $this->actingAs($stranger)
            ->delete(route('account.memory.events.destroy', $event->id))
            ->assertNotFound();

        $this->assertDatabaseHas('artwork_saves', ['id' => $save->id]);
        $this->assertDatabaseHas('memory_events', ['id' => $event->id]);
    }

    public function testConsentRenewalTurnsMemoryBackOn(): void
    {
        $user = User::factory()->create();
        MemoryConsent::factory()
            ->stale()
            ->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('account.consent'))
            ->assertOk()
            ->assertSee('Renew the agreement and turn memory back on');

        $this->actingAs($user)->post(route('account.consent.renew'))->assertRedirect(route('account.trails'));

        $this->assertTrue(app(ConsentService::class)->hasCurrentConsent($user->fresh()));
        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::CONSENT_RENEWED)->count());

        $this->actingAs($user)->get(route('account.trails'))->assertOk();
    }

    public function testRenewalAfterRevocationReopensTheSameVersion(): void
    {
        $user = User::factory()->create();
        MemoryConsent::factory()
            ->revoked()
            ->create(['user_id' => $user->id]);

        $this->actingAs($user)->post(route('account.consent.renew'))->assertRedirect(route('account.trails'));

        $this->assertTrue(app(ConsentService::class)->hasCurrentConsent($user->fresh()));
        $this->assertSame(1, $user->memoryConsents()->count(), 'Re-granting a revoked version reopens its row');
    }
}
