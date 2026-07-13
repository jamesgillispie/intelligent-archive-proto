<?php

namespace Database\Factories\Memory;

use App\Models\User;
use App\Models\Memory\ResearchTrail;
use App\Models\Memory\Recommendation;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recommendation>
 */
class RecommendationFactory extends Factory
{
    protected $model = Recommendation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'research_trail_id' => ResearchTrail::factory(),
            'artwork_id' => Artwork::factory(),
            'kind' => 'continuation',
            'reason' => 'Also by ' . $this->faker->name(),
            'citations' => [['artwork_id' => 1, 'field' => 'artist', 'value' => $this->faker->name()]],
            'context' => ['trail_title' => 'A trail', 'item_count' => 3],
            'status' => Recommendation::STATUS_SHOWN,
        ];
    }

    public function accepted(): self
    {
        return $this->state(['status' => Recommendation::STATUS_ACCEPTED, 'responded_at' => now()]);
    }

    public function dismissed(): self
    {
        return $this->state(['status' => Recommendation::STATUS_DISMISSED, 'responded_at' => now()]);
    }
}
