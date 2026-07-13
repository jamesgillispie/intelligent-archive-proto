<?php

namespace Database\Factories\Archive;

use App\Models\User;
use App\Models\Archive\ArtworkEnrichment;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtworkEnrichment>
 */
class ArtworkEnrichmentFactory extends Factory
{
    protected $model = ArtworkEnrichment::class;

    public function definition(): array
    {
        return [
            'artwork_id' => Artwork::factory(),
            'field' => 'description',
            'generated_text' => $this->faker->paragraph(),
            'model_ref' => 'test-model',
            'prompt_ref' => 'test-prompt',
            'status' => ArtworkEnrichment::STATUS_PENDING,
        ];
    }

    public function approved(): self
    {
        return $this->state(
            fn() => [
                'status' => ArtworkEnrichment::STATUS_APPROVED,
                'reviewed_by' => User::factory(),
                'reviewed_at' => now(),
            ],
        );
    }

    public function rejected(): self
    {
        return $this->state(
            fn() => [
                'status' => ArtworkEnrichment::STATUS_REJECTED,
                'reviewed_by' => User::factory(),
                'reviewed_at' => now(),
            ],
        );
    }
}
