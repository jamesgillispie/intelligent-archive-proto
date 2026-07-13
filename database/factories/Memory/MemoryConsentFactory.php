<?php

namespace Database\Factories\Memory;

use App\Models\User;
use App\Models\Memory\MemoryConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemoryConsent>
 */
class MemoryConsentFactory extends Factory
{
    protected $model = MemoryConsent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'consent_version' => config('memory.consent_version'),
            'granted_at' => now(),
        ];
    }

    public function stale(): static
    {
        return $this->state(fn() => ['consent_version' => 'superseded-version']);
    }

    public function revoked(): static
    {
        return $this->state(fn() => ['revoked_at' => now()]);
    }
}
