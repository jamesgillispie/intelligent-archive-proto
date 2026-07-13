<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\MemoryConsent;
use Illuminate\Support\Facades\Route;
use App\Services\Memory\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConsentedMiddlewareTest extends TestCase
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

    public function testConsentedUsersReachTrails(): void
    {
        $this->actingAs($this->consentedUser())->get(route('account.trails'))->assertOk()->assertSee('Your trails');
    }

    public function testStaleConsentVersionIsRedirectedToRenewal(): void
    {
        $user = User::factory()->create();
        MemoryConsent::factory()
            ->stale()
            ->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('account.trails'))->assertRedirect(route('account.consent'));

        $this->actingAs($user)
            ->get(route('account.consent'))
            ->assertOk()
            ->assertSee('The memory agreement has changed');
    }

    public function testRevokedConsentIsRedirectedToRenewal(): void
    {
        $user = User::factory()->create();
        MemoryConsent::factory()
            ->revoked()
            ->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('account.trails'))->assertRedirect(route('account.consent'));
    }

    public function testPausedMemoryAllowsReadsButBlocksWrites(): void
    {
        Route::post('/account/_test-write', fn() => 'written')->middleware(['private', 'auth', 'consented']);

        $user = $this->consentedUser();
        $user->personalizationSetting->update(['memory_paused' => true]);

        $this->actingAs($user)->get(route('account.trails'))->assertOk();

        $this->actingAs($user)->post('/account/_test-write')->assertForbidden();
    }

    public function testUnpausedMemoryAllowsWrites(): void
    {
        Route::post('/account/_test-write', fn() => 'written')->middleware(['private', 'auth', 'consented']);

        $this->actingAs($this->consentedUser())->post('/account/_test-write')->assertOk();
    }
}
