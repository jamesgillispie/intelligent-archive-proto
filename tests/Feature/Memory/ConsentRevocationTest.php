<?php

namespace Tests\Feature\Memory;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\MemoryEvent;
use App\Services\Trails\TrailService;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Revocation completes the grant/renew/pause/revoke lifecycle: the
 * agreement ends, everything held under it is deleted, and the account
 * remains — free to agree again later, starting from nothing.
 */
class ConsentRevocationTest extends TestCase
{
    use RefreshDatabase;

    private function consentedUser(): User
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        return $user;
    }

    public function testRevocationDeletesEverythingAndTurnsMemoryOff(): void
    {
        $user = $this->consentedUser();
        app(TrailService::class)->start($user, 'Dutch Interiors', null, Artwork::factory()->create());

        $this->actingAs($user)
            ->get(route('account.consent.revoke'))
            ->assertOk()
            ->assertSee('Revoke the memory agreement?')
            ->assertSee('1 trail');

        $this->actingAs($user)->delete(route('account.consent.destroy'))->assertRedirect(route('account.memory'));

        $user->refresh();

        $this->assertFalse(app(ConsentService::class)->hasCurrentConsent($user));
        $this->assertNotNull($user->memoryConsents()->sole()->revoked_at);
        $this->assertSame(0, $user->trails()->count());
        $this->assertSame([], app(RedisIrisMemoryContract::class)->inspect("user:{$user->id}:"));

        // Two receipts, in order: the deletion, then the revocation.
        $this->assertSame(
            [MemoryEvent::MEMORY_DELETED, MemoryEvent::CONSENT_REVOKED],
            $user->memoryEvents()->orderBy('id')->pluck('event_type')->all(),
        );

        // Working surfaces are gated; inspection stays open.
        $this->actingAs($user)->get(route('account.trails'))->assertRedirect(route('account.consent'));
        $this->actingAs($user)->get(route('account.memory'))->assertOk();
    }

    public function testAgreeingAgainAfterRevocationStartsFromNothing(): void
    {
        $user = $this->consentedUser();
        app(TrailService::class)->start($user, 'Gone Soon');

        app(ConsentService::class)->revoke($user);

        $this->actingAs($user)->post(route('account.consent.renew'))->assertRedirect(route('account.trails'));

        $user->refresh();

        $this->assertTrue(app(ConsentService::class)->hasCurrentConsent($user));
        $this->assertSame(1, $user->memoryConsents()->count(), 'Re-granting reopens the same version row');
        $this->assertSame(0, $user->trails()->count(), 'Nothing held before revocation returns');

        $this->actingAs($user)->get(route('account.trails'))->assertOk();
    }
}
