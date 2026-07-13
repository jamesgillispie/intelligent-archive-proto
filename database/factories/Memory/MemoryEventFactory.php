<?php

namespace Database\Factories\Memory;

use App\Models\User;
use App\Models\Memory\MemoryEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemoryEvent>
 */
class MemoryEventFactory extends Factory
{
    protected $model = MemoryEvent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_type' => MemoryEvent::ARTWORK_SAVED,
            'payload' => ['artwork_title' => ucwords($this->faker->words(3, true))],
            'occurred_at' => now(),
        ];
    }
}
