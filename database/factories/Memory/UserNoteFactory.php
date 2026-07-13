<?php

namespace Database\Factories\Memory;

use App\Models\User;
use App\Models\Memory\UserNote;
use App\Models\Memory\ResearchTrail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserNote>
 */
class UserNoteFactory extends Factory
{
    protected $model = UserNote::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notable_type' => ResearchTrail::class,
            'notable_id' => ResearchTrail::factory(),
            'body' => $this->faker->sentence(),
        ];
    }
}
