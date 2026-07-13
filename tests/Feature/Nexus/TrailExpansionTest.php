<?php

namespace Tests\Feature\Nexus;

use Tests\TestCase;
use InvalidArgumentException;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Mediums\Models\Medium;
use App\Services\Nexus\Artifacts\TrailSummary;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Artifacts\RelatedArtwork;
use App\Services\Nexus\Contracts\NexusRetriever;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Twill\Capsules\Classifications\Models\Classification;

/**
 * The fake's expansion and comparison behavior — the quality bar a live
 * client must meet: deterministic, explained, cited, and honest about
 * what counts as deepening versus broadening.
 */
class TrailExpansionTest extends TestCase
{
    use RefreshDatabase;

    private Artist $cole;

    private Classification $painting;

    private Medium $oil;

    private Medium $watercolor;

    /** @var list<Artwork> */
    private array $trailWorks;

    private Artwork $continuation;

    private Artwork $counterpoint;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cole = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);
        $this->painting = Classification::create([
            'published' => true,
            'name' => 'painting',
            'source_name' => 'Painting',
        ]);
        $this->oil = Medium::create(['published' => true, 'name' => 'oil on canvas', 'source_name' => 'oil on canvas']);
        $this->watercolor = Medium::create([
            'published' => true,
            'name' => 'watercolor',
            'source_name' => 'watercolor',
        ]);

        $this->trailWorks = [];

        foreach ([1830, 1834, 1838] as $year) {
            $work = Artwork::factory()
                ->withPrimaryImage()
                ->create([
                    'source_begin_year' => $year,
                    'classification_id' => $this->painting->id,
                    'medium_id' => $this->oil->id,
                ]);
            $work->artists()->attach($this->cole->id, ['role' => 'artist']);
            $this->trailWorks[] = $work;
        }

        // Deepens the thread: same hand, same medium, same period.
        $this->continuation = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'The Oxbow, Again',
                'source_title' => 'The Oxbow, Again',
                'source_begin_year' => 1836,
                'classification_id' => $this->painting->id,
                'medium_id' => $this->oil->id,
            ]);
        $this->continuation->artists()->attach($this->cole->id, ['role' => 'artist']);

        // Broadens it: still a painting, but another hand, another medium,
        // another century.
        $this->counterpoint = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'Distant Weather',
                'source_title' => 'Distant Weather',
                'source_begin_year' => 1950,
                'classification_id' => $this->painting->id,
                'medium_id' => $this->watercolor->id,
                'medium_text' => 'watercolor',
            ]);
    }

    private function summary(array $exclude = []): TrailSummary
    {
        return new TrailSummary(
            artworkIds: array_map(fn(Artwork $work) => $work->id, $this->trailWorks),
            excludeArtworkIds: $exclude,
        );
    }

    public function testExpansionReturnsCitedContinuationsAndCounterpoints(): void
    {
        $expansion = app(NexusRetriever::class)->trailExpansion($this->summary());

        $this->assertFalse($expansion->isEmpty());

        $continuationIds = array_map(fn(RelatedArtwork $m) => $m->artworkId, $expansion->continuations);
        $this->assertContains($this->continuation->id, $continuationIds);

        $top = $expansion->continuations[0];
        $this->assertSame(RelatedArtwork::KIND_CONTINUATION, $top->kind);
        $this->assertStringContainsString('Thomas Cole', $top->reason);
        $this->assertGreaterThan(0, count($top->citations));

        $counterpointIds = array_map(fn(RelatedArtwork $m) => $m->artworkId, $expansion->counterpoints);
        $this->assertContains($this->counterpoint->id, $counterpointIds);

        $counter = $expansion->counterpoints[0];
        $this->assertSame(RelatedArtwork::KIND_COUNTERPOINT, $counter->kind);
        $this->assertStringContainsString('counterpoint', $counter->reason);
        $this->assertStringContainsString('still painting', $counter->reason);
        $this->assertGreaterThan(0, count($counter->citations));

        // The trail's own works are never suggested back.
        foreach ($this->trailWorks as $work) {
            $this->assertNotContains($work->id, $expansion->artworkIds());
        }
    }

    public function testExclusionsAreRespected(): void
    {
        $expansion = app(NexusRetriever::class)->trailExpansion(
            $this->summary(exclude: [$this->continuation->id, $this->counterpoint->id]),
        );

        $this->assertNotContains($this->continuation->id, $expansion->artworkIds());
        $this->assertNotContains($this->counterpoint->id, $expansion->artworkIds());
    }

    public function testExpansionIsDeterministic(): void
    {
        $first = app(NexusRetriever::class)->trailExpansion($this->summary());
        $again = app(NexusRetriever::class)->trailExpansion($this->summary());

        $shape = fn(RelatedArtwork $m) => [$m->artworkId, $m->kind, $m->score];

        $this->assertEquals(array_map($shape, $first->all()), array_map($shape, $again->all()));
    }

    public function testCompareGroundsEveryClaimInBothWorks(): void
    {
        $bierstadt = Artist::factory()->create(['name' => 'Albert Bierstadt', 'source_name' => 'Albert Bierstadt']);
        $late = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'source_begin_year' => 1920,
                'classification_id' => $this->painting->id,
                'medium_id' => $this->oil->id,
            ]);
        $late->artists()->attach($bierstadt->id, ['role' => 'artist']);

        $early = $this->trailWorks[0]->fresh(['artists', 'classification', 'medium']);

        $comparison = app(NexusRetriever::class)->compare([
            $early,
            $late->fresh(['artists', 'classification', 'medium']),
        ]);

        $this->assertSame([$early->id, $late->id], $comparison->artworkIds);
        $this->assertFalse($comparison->isEmpty());

        $similarities = array_map(fn($claim) => $claim->statement, $comparison->similarities);
        $differences = array_map(fn($claim) => $claim->statement, $comparison->differences);

        $this->assertContains('Both are classified as painting', $similarities);
        $this->assertContains('Both are oil on canvas', $similarities);
        $this->assertContains('Made about 90 years apart', $differences);
        $this->assertNotEmpty(array_filter($differences, fn($s) => str_contains($s, 'By different hands')));

        // Every claim cites every compared work.
        foreach ([...$comparison->similarities, ...$comparison->differences] as $claim) {
            $this->assertCount(2, $claim->citations);
        }
    }

    public function testCompareNeedsAtLeastTwoWorks(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(NexusRetriever::class)->compare([$this->trailWorks[0]]);
    }
}
