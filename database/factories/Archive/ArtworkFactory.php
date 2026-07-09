<?php

namespace Database\Factories\Archive;

use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artwork>
 */
class ArtworkFactory extends Factory
{
    protected $model = Artwork::class;

    public function definition(): array
    {
        $title = ucwords($this->faker->words(3, true));
        $year = $this->faker->numberBetween(1750, 1950);

        return [
            'published' => true,
            'nga_object_id' => (string) $this->faker->unique()->numberBetween(1, 999999),
            'accession_number' => $this->faker->unique()->bothify('####.##.###'),
            'title' => $title,
            'source_title' => $title,
            'display_date' => (string) $year,
            'source_display_date' => (string) $year,
            'medium_text' => 'oil on canvas',
            'source_medium_text' => 'oil on canvas',
            'credit_line' => 'Test Collection',
            'source_credit_line' => 'Test Collection',
            'attribution' => $this->faker->name(),
            'source_begin_year' => $year,
            'source_end_year' => $year,
            'source_classification' => 'Painting',
            'is_displayable' => true,
            'last_synced_at' => now(),
        ];
    }

    public function withPrimaryImage(): self
    {
        return $this->afterCreating(function (Artwork $artwork) {
            $artwork->images()->create([
                'nga_uuid' => $this->faker->uuid(),
                'iiif_url' => 'https://api.nga.gov/iiif/' . $this->faker->uuid(),
                'iiif_thumb_url' => 'https://api.nga.gov/iiif/' . $this->faker->uuid() . '/full/!200,200/0/default.jpg',
                'view_type' => 'primary',
                'is_primary' => true,
                'width' => 3000,
                'height' => 2400,
            ]);
        });
    }
}
