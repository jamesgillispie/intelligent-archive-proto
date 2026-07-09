<?php

namespace Database\Factories\Archive;

use App\Twill\Capsules\Artists\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artist>
 */
class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    public function definition(): array
    {
        $name = $this->faker->name();
        $born = $this->faker->numberBetween(1700, 1900);

        return [
            'published' => true,
            'nga_constituent_id' => (string) $this->faker->unique()->numberBetween(1, 999999),
            'name' => $name,
            'source_name' => $name,
            'display_date' => $born . ' - ' . ($born + 70),
            'source_display_date' => $born . ' - ' . ($born + 70),
            'nationality' => 'American',
            'begin_year' => $born,
            'end_year' => $born + 70,
            'artist_of_nga_object' => true,
            'last_synced_at' => now(),
        ];
    }
}
