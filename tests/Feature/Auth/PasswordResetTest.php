<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function testForgotPasswordScreenRenders(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Reset your password');
    }

    public function testResetLinkIsSent(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function testPasswordCanBeResetWithValidToken(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->get(route('password.reset', ['token' => $token]) . '?email=' . urlencode($user->email))
            ->assertOk()
            ->assertSee('Choose a new password');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'a-brand-new-password',
            'password_confirmation' => 'a-brand-new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('a-brand-new-password', $user->fresh()->password));
    }
}
