<?php

namespace Database\Factories\Memory;

use App\Models\User;
use App\Models\Memory\ArtworkSave;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtworkSave>
 */
class ArtworkSaveFactory extends Factory
{
    protected $model = ArtworkSave::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'artwork_id' => Artwork::factory(),
        ];
    }
}
