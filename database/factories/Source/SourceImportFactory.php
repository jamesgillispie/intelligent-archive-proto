<?php

namespace Database\Factories\Source;

use App\Models\Source\SourceImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SourceImport>
 */
class SourceImportFactory extends Factory
{
    protected $model = SourceImport::class;

    public function definition(): array
    {
        return [
            'snapshot_ref' => $this->faker->sha1(),
            'data_path' => storage_path('app/nga'),
            'status' => SourceImport::STATUS_COMPLETED,
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ];
    }
}
