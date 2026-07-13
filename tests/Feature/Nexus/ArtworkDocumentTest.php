<?php

namespace Tests\Feature\Nexus;

use Tests\TestCase;
use App\Events\ArtworkPromoted;
use App\Jobs\Indexing\IndexArtwork;
use App\Models\Source\SourceRecord;
use Illuminate\Support\Facades\Bus;
use App\Services\Nexus\Fake\FakeNexus;
use App\Models\Archive\ArtworkEnrichment;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Services\Nexus\ArtworkDocumentBuilder;
use App\Services\Nexus\Contracts\NexusIndexer;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Twill\Capsules\Artworks\Repositories\ArtworkRepository;

/**
 * Document building (canonical-only, approved-text-only) and the queued
 * indexing pipeline that keeps Nexus in step with the canonical layer.
 */
class ArtworkDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function fake(): FakeNexus
    {
        $indexer = app(NexusIndexer::class);
        assert($indexer instanceof FakeNexus);

        return $indexer;
    }

    public function testDocumentsCarryCanonicalFieldsIdentifiedPassagesAndEdges(): void
    {
        $artist = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);
        $artwork = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'The Oxbow, After a Storm',
                'source_title' => 'The Oxbow, After a Storm',
                'description' => '<p>Editorial text about the light.</p>',
                'source_inscription' => 'lower left: T. Cole',
            ]);
        $artwork->artists()->attach($artist->id, ['role' => 'painter']);

        $enrichment = ArtworkEnrichment::factory()
            ->approved()
            ->create([
                'artwork_id' => $artwork->id,
                'field' => 'description',
                'generated_text' => 'A reviewed note on the composition.',
            ]);

        $document = app(ArtworkDocumentBuilder::class)->build($artwork->fresh());

        $this->assertSame($artwork->id, $document->artworkId);
        $this->assertSame($artwork->nga_object_id, $document->ngaObjectId);

        $this->assertSame('The Oxbow, After a Storm', $document->fields['title']);
        $this->assertSame('Thomas Cole', $document->fields['artists']);
        $this->assertSame('oil on canvas', $document->fields['medium']);

        $this->assertSame([$artist->id], $document->edges['artist_ids']);

        $passages = collect($document->passages)->keyBy('id');

        // Markup is stripped; the passage id makes the text citable.
        $this->assertSame('Editorial text about the light.', $passages['description']['text']);
        $this->assertSame('lower left: T. Cole', $passages['inscription']['text']);
        $this->assertSame('A reviewed note on the composition.', $passages["enrichment:{$enrichment->id}"]['text']);
        $this->assertSame('approved_enrichment:description', $passages["enrichment:{$enrichment->id}"]['kind']);
    }

    public function testUnapprovedTextAndRawSourcePayloadsNeverEnterTheDocument(): void
    {
        $artwork = Artwork::factory()->withPrimaryImage()->create();

        // Raw source payload with a key the canonicalizer never promotes.
        $record = SourceRecord::factory()
            ->forObject([
                'objectid' => (string) $artwork->nga_object_id,
                'title' => $artwork->title,
                'locationid' => 'RAW-ONLY-MARKER',
            ])
            ->create();
        $artwork->update(['source_record_id' => $record->id]);

        ArtworkEnrichment::factory()->create([
            'artwork_id' => $artwork->id,
            'generated_text' => 'PENDING-MARKER text awaiting review.',
        ]);
        ArtworkEnrichment::factory()
            ->rejected()
            ->create([
                'artwork_id' => $artwork->id,
                'generated_text' => 'REJECTED-MARKER text a reviewer declined.',
            ]);
        ArtworkEnrichment::factory()
            ->approved()
            ->create([
                'artwork_id' => $artwork->id,
                'generated_text' => 'APPROVED-MARKER text a reviewer accepted.',
            ]);

        $json = (string) json_encode(app(ArtworkDocumentBuilder::class)->build($artwork->fresh()));

        $this->assertStringContainsString('APPROVED-MARKER', $json);
        $this->assertStringNotContainsString('PENDING-MARKER', $json);
        $this->assertStringNotContainsString('REJECTED-MARKER', $json);
        $this->assertStringNotContainsString('RAW-ONLY-MARKER', $json);
    }

    public function testIndexingIsIdempotentAndRemovesUndisplayableArtworks(): void
    {
        $artwork = Artwork::factory()->withPrimaryImage()->create();

        IndexArtwork::dispatch($artwork->id);
        IndexArtwork::dispatch($artwork->id);

        $this->assertCount(1, $this->fake()->documents());
        $this->assertArrayHasKey($artwork->id, $this->fake()->documents());

        // Unpublishing removes the document on the next run.
        $artwork->update(['published' => false]);
        IndexArtwork::dispatch($artwork->id);

        $this->assertSame([], $this->fake()->documents());

        // A vanished artwork removes rather than errors.
        $id = $artwork->id;
        $artwork->delete();
        IndexArtwork::dispatch($id);

        $this->assertSame([], $this->fake()->documents());
    }

    public function testPromotionQueuesIndexingThroughTheEvent(): void
    {
        Bus::fake([IndexArtwork::class]);

        ArtworkPromoted::dispatch(42);

        Bus::assertDispatched(IndexArtwork::class, fn(IndexArtwork $job) => $job->artworkId === 42);
    }

    public function testEditorialSavesThroughTwillReindex(): void
    {
        $artwork = Artwork::factory()->withPrimaryImage()->create();

        Bus::fake([IndexArtwork::class]);

        app(ArtworkRepository::class)->update($artwork->id, ['title' => 'Refined Editorial Title']);

        Bus::assertDispatched(IndexArtwork::class, fn(IndexArtwork $job) => $job->artworkId === $artwork->id);
    }

    public function testImportIndexesTheDisplayableSubsetEndToEnd(): void
    {
        // Promotion → ArtworkPromoted → listener → sync queue → FakeNexus.
        $this->artisan('nga:import', [
            '--path' => base_path('tests/Fixtures/nga'),
            '--limit' => 10,
            '--sync' => true,
            '--skip-images' => true,
        ])->assertSuccessful();

        $documents = $this->fake()->documents();

        $this->assertSame(Artwork::displayable()->orderBy('id')->pluck('id')->all(), array_keys($documents));

        foreach ($documents as $document) {
            $this->assertNotSame('', $document->ngaObjectId);
            $this->assertArrayHasKey('title', $document->fields);
        }
    }
}
