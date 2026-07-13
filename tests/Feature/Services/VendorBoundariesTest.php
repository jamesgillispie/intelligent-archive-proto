<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\Nexus\Fake\FakeNexus;
use App\Services\Nexus\Artifacts\Citation;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Services\Nexus\Contracts\NexusIndexer;
use App\Services\RedisIris\FakeRedisIrisMemory;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Artifacts\CitationBundle;
use App\Services\Nexus\Artifacts\RelatedArtwork;
use App\Services\Nexus\Contracts\NexusRetriever;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;

class VendorBoundariesTest extends TestCase
{
    use RefreshDatabase;

    public function testFakeBindingsResolveAsOneSingletonAcrossBothNexusContracts(): void
    {
        $indexer = app(NexusIndexer::class);
        $retriever = app(NexusRetriever::class);
        $iris = app(RedisIrisMemoryContract::class);

        $this->assertInstanceOf(FakeNexus::class, $indexer);
        $this->assertInstanceOf(FakeNexus::class, $retriever);
        // One instance serves both sides, so sync-queue tests can index
        // through NexusIndexer and assert through the same fake.
        $this->assertSame($indexer, $retriever);
        $this->assertInstanceOf(FakeRedisIrisMemory::class, $iris);
    }

    public function testLiveDriversFailLoudlyUntilImplemented(): void
    {
        config(['nexus.driver' => 'live']);
        $this->app->forgetInstance(NexusRetriever::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not implemented');

        app(NexusRetriever::class);
    }

    public function testIrisFakeHonorsTheInspectAndPurgeLifecycle(): void
    {
        $iris = app(RedisIrisMemoryContract::class);

        $iris->put('user:7:active_trail', ['trail_id' => 3]);
        $iris->put('user:7:recommendation_context', ['themes' => ['landscape']]);
        $iris->put('user:8:active_trail', ['trail_id' => 9]);

        $this->assertSame(['trail_id' => 3], $iris->get('user:7:active_trail'));
        $this->assertCount(2, $iris->inspect('user:7:'));

        $iris->purge('user:7:');

        $this->assertSame([], $iris->inspect('user:7:'));
        $this->assertNull($iris->get('user:7:active_trail'));
        // Other subjects are untouched.
        $this->assertSame(['trail_id' => 9], $iris->get('user:8:active_trail'));
    }

    public function testIrisFakeExpiresContext(): void
    {
        $iris = app(RedisIrisMemoryContract::class);

        $iris->put('session:abc:anonymous_context', ['intent' => 'storms'], ttlSeconds: -1);

        $this->assertNull($iris->get('session:abc:anonymous_context'));
    }

    public function testFakeNexusReturnsCitedExplainableMatches(): void
    {
        $artist = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);

        $subject = Artwork::factory()
            ->withPrimaryImage()
            ->create(['source_begin_year' => 1836]);
        $related = Artwork::factory()
            ->withPrimaryImage()
            ->create(['source_begin_year' => 1840]);
        $unrelated = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'source_begin_year' => 1600,
                'medium_text' => 'tempera on panel',
            ]);

        $subject->artists()->attach($artist->id, ['role' => 'artist']);
        $related->artists()->attach($artist->id, ['role' => 'artist']);

        // Force distinct vocab so the unrelated work shares nothing.
        $subject->update(['classification_id' => null, 'medium_id' => null, 'department_id' => null]);
        $related->update(['classification_id' => null, 'medium_id' => null, 'department_id' => null]);
        $unrelated->update(['classification_id' => null, 'medium_id' => null, 'department_id' => null]);

        $matches = app(NexusRetriever::class)->relatedTo($subject->fresh('artists'));

        $this->assertSame($subject->id, $matches->subjectArtworkId);
        $this->assertFalse($matches->isEmpty());

        $top = $matches->related[0];
        $this->assertInstanceOf(RelatedArtwork::class, $top);
        $this->assertSame($related->id, $top->artworkId);
        $this->assertSame(RelatedArtwork::KIND_SHARED_ARTIST, $top->kind);
        $this->assertStringContainsString('Thomas Cole', $top->reason);
        $this->assertGreaterThan(0, count($top->citations));
        $this->assertNotContains($unrelated->id, $matches->artworkIds());

        // Determinism: same inputs, same ranked output.
        $again = app(NexusRetriever::class)->relatedTo($subject->fresh('artists'));
        $this->assertEquals(
            array_map(fn(RelatedArtwork $m) => [$m->artworkId, $m->score], $matches->related),
            array_map(fn(RelatedArtwork $m) => [$m->artworkId, $m->score], $again->related),
        );
    }

    public function testACitationBundleCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CitationBundle([]);
    }

    public function testCitationsQuoteTheGroundingValue(): void
    {
        $citation = new Citation(artworkId: 5, field: 'medium', value: 'oil on canvas');

        $this->assertSame(['artwork_id' => 5, 'field' => 'medium', 'value' => 'oil on canvas'], $citation->toArray());
    }
}
