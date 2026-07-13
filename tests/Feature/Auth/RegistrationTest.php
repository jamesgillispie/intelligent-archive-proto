<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\MemoryConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_INPUT = [
        'name' => 'Ada Researcher',
        'email' => 'ada@example.org',
        'password' => 'a-long-quiet-password',
        'password_confirmation' => 'a-long-quiet-password',
    ];

    public function testRegisterScreenCarriesTheMemoryContract(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('What an account means')
            ->assertSee('There is no background profiling', false)
            ->assertSee('/methodology')
            ->assertSee('Create account and turn memory on');
    }

    public function testRegistrationCreatesUserAndConsentAtomically(): void
    {
        $response = $this->post(route('register'), self::VALID_INPUT);

        $response->assertRedirect(route('account.memory-activated'));
        $this->assertAuthenticated();

        $user = User::where('email', 'ada@example.org')->firstOrFail();

        $consent = $user->memoryConsent;
        $this->assertNotNull($consent, 'Registration must create the consent record');
        $this->assertSame(config('memory.consent_version'), $consent->consent_version);
        $this->assertNotNull($consent->granted_at);
        $this->assertNull($consent->revoked_at);

        $settings = $user->personalizationSetting;
        $this->assertNotNull($settings, 'Registration must seed personalization settings');
        $this->assertFalse($settings->memory_paused);
        $this->assertTrue($settings->semantic_suggestions_enabled);
    }

    public function testConsentRecordsTheConfiguredVersion(): void
    {
        config()->set('memory.consent_version', '2099-01-01-test');

        $this->post(route('register'), self::VALID_INPUT);

        $this->assertSame('2099-01-01-test', MemoryConsent::firstOrFail()->consent_version);
    }

    public function testInvalidRegistrationCreatesNothing(): void
    {
        User::factory()->create(['email' => 'ada@example.org']);

        $this->from(route('register'))
            ->post(route('register'), self::VALID_INPUT)
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, User::count());
        $this->assertSame(0, MemoryConsent::count());
    }

    public function testRegistrationConfirmationIsShownExactlyOnce(): void
    {
        $this->post(route('register'), self::VALID_INPUT);

        $this->get(route('account.memory-activated'))->assertOk()->assertSee('Memory is now on');

        $this->get(route('account.memory-activated'))->assertRedirect(route('archive.index'));
    }
}
