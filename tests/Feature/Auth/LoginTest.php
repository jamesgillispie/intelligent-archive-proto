<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Services\Memory\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginScreenRenders(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in')->assertSee('where you left them');
    }

    public function testUsersLandOnTrailsAfterLogin(): void
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/account/trails');

        $this->assertAuthenticatedAs($user);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'not-the-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function testAuthenticatedUsersAreRedirectedAwayFromAuthScreens(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('login'))->assertRedirect('/account/trails');
        $this->actingAs($user)->get(route('register'))->assertRedirect('/account/trails');
    }

    public function testLogout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect('/');

        $this->assertGuest();
    }
}
