<?php

namespace Database\Factories\Memory;

use App\Models\User;
use App\Models\Memory\PersonalizationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonalizationSetting>
 */
class PersonalizationSettingFactory extends Factory
{
    protected $model = PersonalizationSetting::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'memory_paused' => false,
            'semantic_suggestions_enabled' => true,
        ];
    }

    public function paused(): static
    {
        return $this->state(fn() => ['memory_paused' => true]);
    }
}
