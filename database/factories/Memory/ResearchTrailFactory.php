<?php

namespace Database\Factories\Memory;

use App\Models\User;
use App\Models\Memory\ResearchTrail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResearchTrail>
 */
class ResearchTrailFactory extends Factory
{
    protected $model = ResearchTrail::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => ucwords($this->faker->words(3, true)),
            'intent_note' => null,
            'status' => ResearchTrail::STATUS_ACTIVE,
            'last_activity_at' => now(),
        ];
    }

    public function archived(): self
    {
        return $this->state(['status' => ResearchTrail::STATUS_ARCHIVED]);
    }
}
