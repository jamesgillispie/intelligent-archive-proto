<?php

namespace Database\Factories\Source;

use App\Models\Source\SourceImport;
use App\Models\Source\SourceRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SourceRecord>
 */
class SourceRecordFactory extends Factory
{
    protected $model = SourceRecord::class;

    public function definition(): array
    {
        $payload = [
            'objectid' => (string) $this->faker->unique()->numberBetween(1, 999999),
            'title' => $this->faker->sentence(3),
            'classification' => 'Painting',
        ];

        return [
            'source_import_id' => SourceImport::factory(),
            'source_table' => SourceRecord::TABLE_OBJECTS,
            'source_id' => $payload['objectid'],
            'payload' => $payload,
            'content_hash' => hash('sha256', json_encode($payload)),
            'version' => 1,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ];
    }

    public function forObject(array $payload): self
    {
        return $this->state(
            fn() => [
                'source_table' => SourceRecord::TABLE_OBJECTS,
                'source_id' => $payload['objectid'],
                'payload' => $payload,
                'content_hash' => hash('sha256', json_encode($payload)),
            ],
        );
    }
}
