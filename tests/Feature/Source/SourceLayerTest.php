<?php

namespace Tests\Feature\Source;

use Tests\TestCase;
use App\Models\Source\SourceImage;
use App\Models\Source\SourceImport;
use App\Models\Source\SourceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SourceLayerTest extends TestCase
{
    use RefreshDatabase;

    public function testSourceRecordPreservesRawPayloadAndBelongsToImport(): void
    {
        $record = SourceRecord::factory()->create([
            'payload' => ['objectid' => '42', 'title' => 'The Shipwreck', 'medium' => 'oil on canvas'],
            'source_id' => '42',
        ]);

        $this->assertSame('The Shipwreck', $record->fresh()->payloadValue('title'));
        $this->assertSame('oil on canvas', $record->fresh()->payload['medium']);
        $this->assertInstanceOf(SourceImport::class, $record->import);
        $this->assertTrue($record->import->records()->whereKey($record->id)->exists());
    }

    public function testSourceTablePlusSourceIdIsUnique(): void
    {
        SourceRecord::factory()->create(['source_table' => 'objects', 'source_id' => '7']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        SourceRecord::factory()->create(['source_table' => 'objects', 'source_id' => '7']);
    }

    public function testSourceImageScopesToPublishedImagesRows(): void
    {
        SourceRecord::factory()->create(['source_table' => SourceRecord::TABLE_OBJECTS, 'source_id' => '1']);

        $import = SourceImport::factory()->create();
        SourceImage::create([
            'source_import_id' => $import->id,
            'source_id' => 'uuid-123',
            'payload' => [
                'uuid' => 'uuid-123',
                'iiifurl' => 'https://api.nga.gov/iiif/abc',
                'iiifthumburl' => 'https://api.nga.gov/iiif/abc/full/!200,200/0/default.jpg',
                'viewtype' => 'primary',
                'depictstmsobjectid' => '1',
            ],
            'content_hash' => str_repeat('a', 64),
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);

        $this->assertSame(1, SourceImage::count());
        $this->assertSame(2, SourceRecord::count());

        $image = SourceImage::first();
        $this->assertSame(SourceRecord::TABLE_PUBLISHED_IMAGES, $image->source_table);
        $this->assertSame('https://api.nga.gov/iiif/abc', $image->iiifUrl());
        $this->assertSame('primary', $image->viewType());
        $this->assertSame('1', $image->depictsObjectId());
    }

    public function testPresentInSourceScopeExcludesRowsMissingUpstream(): void
    {
        SourceRecord::factory()->create(['source_id' => '1']);
        SourceRecord::factory()->create(['source_id' => '2', 'missing_from_source_at' => now()]);

        $this->assertSame(1, SourceRecord::presentInSource()->count());
    }
}
