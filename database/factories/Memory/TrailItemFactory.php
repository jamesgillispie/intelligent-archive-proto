<?php

namespace Database\Factories\Memory;

use App\Models\Memory\TrailItem;
use App\Models\Memory\ResearchTrail;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrailItem>
 */
class TrailItemFactory extends Factory
{
    protected $model = TrailItem::class;

    public function definition(): array
    {
        return [
            'research_trail_id' => ResearchTrail::factory(),
            'artwork_id' => Artwork::factory(),
            'position' => 1,
            'origin' => TrailItem::ORIGIN_USER_SAVED,
            'note' => null,
        ];
    }
}
