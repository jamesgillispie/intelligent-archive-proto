<?php

namespace Tests\Feature\Import;

use Tests\TestCase;
use App\Models\Source\SourceImport;
use App\Models\Source\SourceRecord;
use Illuminate\Support\Facades\File;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Twill\Capsules\Classifications\Models\Classification;

class NgaImportTest extends TestCase
{
    use RefreshDatabase;

    protected function fixturePath(): string
    {
        return base_path('tests/Fixtures/nga');
    }

    protected function runImport(?string $path = null, int $limit = 10): void
    {
        $this->artisan('nga:import', [
            '--path' => $path ?? $this->fixturePath(),
            '--limit' => $limit,
            '--sync' => true,
        ])->assertSuccessful();
    }

    public function testImportSelectsTheCuratedSubset(): void
    {
        $this->runImport();

        // 100, 101, 104 are accessioned paintings with a primary image.
        // 102 has no image; 103 is a sculpture.
        $this->assertSame(3, Artwork::count());
        $this->assertNull(Artwork::where('nga_object_id', '102')->first());
        $this->assertNull(Artwork::where('nga_object_id', '103')->first());

        $artwork = Artwork::where('nga_object_id', '100')->firstOrFail();

        // Quoted multi-line CSV field survived verbatim in the source layer.
        $this->assertStringContainsString("T. Cole\nsecond line", $artwork->source_inscription);

        $this->assertSame('The Oxbow, After a Storm', $artwork->title);
        $this->assertSame('The Oxbow, After a Storm', $artwork->source_title);
        $this->assertSame('1836', $artwork->display_date);
        $this->assertSame('oil on canvas', $artwork->medium_text);
        $this->assertSame('Painting', $artwork->classification->name);
        $this->assertTrue($artwork->is_displayable);
        $this->assertTrue($artwork->published);

        // Images: primary + alternate, both linked, rights recorded on source.
        $this->assertSame(2, $artwork->images()->count());
        $this->assertSame('https://api.nga.gov/iiif/img-100', $artwork->primaryImage->iiif_url);
        $this->assertSame(
            'CC0 (NGA open access image program)',
            SourceRecord::forTable(SourceRecord::TABLE_PUBLISHED_IMAGES)
                ->where('source_id', 'img-100')
                ->value('rights'),
        );

        // Artist join: painter role only; the donor is excluded.
        $this->assertSame(1, $artwork->artists()->count());
        $this->assertSame('Thomas Cole', $artwork->artists->first()->name);
        $this->assertSame(2, Artist::count());
        $this->assertNull(Artist::where('nga_constituent_id', '950')->first());

        // Provenance anchor exists.
        $this->assertNotNull($artwork->sourceRecord);
        $this->assertSame(SourceImport::STATUS_COMPLETED, SourceImport::first()->status);
    }

    public function testImportRespectsTheLimit(): void
    {
        $this->runImport(limit: 2);

        $this->assertSame(2, Artwork::count());
    }

    public function testReRunIsIdempotent(): void
    {
        $this->runImport();

        $artworks = Artwork::count();
        $sourceRecords = SourceRecord::count();
        $slugs = Artwork::firstOrFail()->slugs()->count();

        $this->runImport();

        $this->assertSame($artworks, Artwork::count());
        $this->assertSame($sourceRecords, SourceRecord::count());
        $this->assertSame(1, (int) SourceRecord::max('version'));
        $this->assertSame($slugs, Artwork::firstOrFail()->slugs()->count());
        $this->assertSame(2, SourceImport::count());
    }

    public function testUpstreamChangeBumpsVersionAndPreservesEditorialOverride(): void
    {
        // Work on a mutable copy of the fixtures.
        $workingPath = storage_path('framework/testing/nga-fixtures');
        File::deleteDirectory($workingPath);
        File::copyDirectory($this->fixturePath(), $workingPath);

        $this->runImport($workingPath);

        $artwork = Artwork::where('nga_object_id', '100')->firstOrFail();

        // Editor overrides the title in Twill.
        $artwork->update(['title' => 'Editorial Title']);

        // Upstream corrects the title in a later snapshot.
        $objects = File::get("{$workingPath}/objects.csv");
        File::put(
            "{$workingPath}/objects.csv",
            str_replace('The Oxbow, After a Storm', 'The Oxbow (Corrected)', $objects),
        );

        $this->runImport($workingPath);

        $artwork->refresh();
        $record = SourceRecord::forTable(SourceRecord::TABLE_OBJECTS)->where('source_id', '100')->firstOrFail();

        $this->assertSame(2, $record->version);
        $this->assertSame('The Oxbow (Corrected)', $artwork->source_title);
        $this->assertSame('Editorial Title', $artwork->title);
        $this->assertTrue($artwork->isOverridden('title'));

        // Un-overridden artworks followed the source (none changed here, so
        // spot-check another field stayed in sync).
        $this->assertSame('1836', $artwork->display_date);
        $this->assertFalse($artwork->isOverridden('display_date'));

        File::deleteDirectory($workingPath);
    }

    public function testUnpublishedArtworkIsNotRepublishedByReImport(): void
    {
        $this->runImport();

        $artwork = Artwork::where('nga_object_id', '100')->firstOrFail();
        $artwork->update(['published' => false]);

        $this->runImport();

        $this->assertFalse($artwork->fresh()->published);
    }

    public function testMissingFilesFailLoudlyWithFilenames(): void
    {
        $this->artisan('nga:import', ['--path' => storage_path('nowhere'), '--sync' => true])
            ->assertFailed()
            ->expectsOutputToContain('objects.csv');

        $this->assertSame(0, Artwork::count());
        $this->assertSame(0, Classification::count());
    }
}
