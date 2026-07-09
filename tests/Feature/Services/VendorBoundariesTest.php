<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\Nexus\ArchiveMatch;
use App\Services\Nexus\FakeNexusArchive;
use App\Services\Nexus\NexusArchiveContract;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Services\RedisIris\FakeRedisIrisMemory;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;

class VendorBoundariesTest extends TestCase
{
    use RefreshDatabase;

    public function testFakeBindingsResolveAsSingletons(): void
    {
        $nexus = app(NexusArchiveContract::class);
        $iris = app(RedisIrisMemoryContract::class);

        $this->assertInstanceOf(FakeNexusArchive::class, $nexus);
        $this->assertInstanceOf(FakeRedisIrisMemory::class, $iris);
        $this->assertSame($nexus, app(NexusArchiveContract::class));
    }

    public function testLiveDriversFailLoudlyUntilImplemented(): void
    {
        config(['nexus.driver' => 'live']);
        $this->app->forgetInstance(NexusArchiveContract::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not implemented');

        app(NexusArchiveContract::class);
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

        $matches = app(NexusArchiveContract::class)->relatedTo($subject->fresh('artists'));

        $this->assertNotEmpty($matches);

        $top = $matches->first();
        $this->assertInstanceOf(ArchiveMatch::class, $top);
        $this->assertSame($related->id, $top->artworkId);
        $this->assertStringContainsString('Thomas Cole', $top->reason);
        $this->assertNotEmpty($top->citations);
        $this->assertFalse($matches->contains(fn(ArchiveMatch $match) => $match->artworkId === $unrelated->id));

        // Determinism: same inputs, same output.
        $again = app(NexusArchiveContract::class)->relatedTo($subject->fresh('artists'));
        $this->assertEquals(
            $matches->map(fn($m) => [$m->artworkId, $m->score])->all(),
            $again->map(fn($m) => [$m->artworkId, $m->score])->all(),
        );
    }

    public function testArchiveMatchRequiresCitations(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ArchiveMatch(artworkId: 1, reason: 'vibes', score: 1.0, citations: []);
    }
}
