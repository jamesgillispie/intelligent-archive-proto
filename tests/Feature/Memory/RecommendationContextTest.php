<?php

namespace Tests\Feature\Memory;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\ResearchTrail;
use App\Models\Memory\Recommendation;
use App\Services\Trails\TrailService;
use App\Services\Memory\ConsentService;
use App\Services\Memory\TrailMemoryService;
use App\Services\Memory\MemoryExportService;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Services\Memory\MemoryDeletionService;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;
use App\Services\Memory\RecommendationContextService;

/**
 * The two Iris namespaces deferred since Phase 5, plus the export and
 * deletion guarantees stretched over everything Phase 7 added.
 */
class RecommendationContextTest extends TestCase
{
    use RefreshDatabase;

    private function consentedUser(): User
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        return $user;
    }

    private function iris(): RedisIrisMemoryContract
    {
        return app(RedisIrisMemoryContract::class);
    }

    private function dismissedRecommendation(User $user): Recommendation
    {
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);

        return Recommendation::factory()
            ->dismissed()
            ->create([
                'user_id' => $user->id,
                'research_trail_id' => $trail->id,
                'artwork_id' => Artwork::factory()->create()->id,
            ]);
    }

    public function testDismissedIdsAreCachedInIrisAndRebuiltFromSql(): void
    {
        $user = $this->consentedUser();
        $recommendation = $this->dismissedRecommendation($user);

        $service = app(RecommendationContextService::class);

        $this->assertSame([$recommendation->artwork_id], $service->dismissedArtworkIds($user));

        $held = $this->iris()->get("user:{$user->id}:recommendation_context");
        $this->assertSame([$recommendation->artwork_id], $held['dismissed_artwork_ids']);

        // The held copy expires on its own; SQL rebuilds it on demand.
        $this->travel((int) config('memory.recommendation_context_ttl_minutes') + 1)->minutes();
        $this->assertSame([], $this->iris()->inspect("user:{$user->id}:"));
        $this->assertSame([$recommendation->artwork_id], $service->dismissedArtworkIds($user));
    }

    public function testPausedUsersKeepSuppressionWithoutHeldContext(): void
    {
        $user = $this->consentedUser();
        $recommendation = $this->dismissedRecommendation($user);

        app(ConsentService::class)->pause($user);

        // Suppression still honored, straight from SQL…
        $this->assertSame(
            [$recommendation->artwork_id],
            app(RecommendationContextService::class)->dismissedArtworkIds($user),
        );

        // …but nothing derived is held while paused.
        $this->assertSame([], $this->iris()->inspect("user:{$user->id}:"));
    }

    public function testSemanticSummaryIsContentHashKeyedAndRebuildsOnChange(): void
    {
        $user = $this->consentedUser();
        $cole = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id, 'title' => 'Hudson Light']);

        $first = Artwork::factory()->create(['source_begin_year' => 1830]);
        $first->artists()->attach($cole->id, ['role' => 'artist']);
        TrailItem::factory()->create(['research_trail_id' => $trail->id, 'artwork_id' => $first->id, 'position' => 1]);

        $summary = app(TrailMemoryService::class)->semanticSummary($trail);

        $this->assertSame([$first->id], $summary['artwork_ids']);
        $this->assertContains('Thomas Cole', $summary['anchors']['artists']);

        $held = $this->iris()->get("trail:{$trail->id}:semantic_summary");
        $this->assertSame($summary['content_hash'], $held['content_hash']);

        // The trail changes; the held hash stops matching; it rebuilds.
        $second = Artwork::factory()->create(['source_begin_year' => 1902]);
        TrailItem::factory()->create(['research_trail_id' => $trail->id, 'artwork_id' => $second->id, 'position' => 2]);

        $rebuilt = app(TrailMemoryService::class)->semanticSummary($trail);

        $this->assertNotSame($summary['content_hash'], $rebuilt['content_hash']);
        $this->assertSame([$first->id, $second->id], $rebuilt['artwork_ids']);
        $this->assertSame('1830–1902', $rebuilt['anchors']['period']);
    }

    public function testTrailContextIsPurgedWithTheTrailAndOnPause(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);
        TrailItem::factory()->create([
            'research_trail_id' => $trail->id,
            'artwork_id' => Artwork::factory()->create()->id,
            'position' => 1,
        ]);

        app(TrailMemoryService::class)->semanticSummary($trail);
        $this->assertNotSame([], $this->iris()->inspect("trail:{$trail->id}:"));

        app(ConsentService::class)->pause($user);
        $this->assertSame([], $this->iris()->inspect("trail:{$trail->id}:"));

        app(ConsentService::class)->resume($user);
        app(TrailMemoryService::class)->semanticSummary($trail->fresh());
        $this->assertNotSame([], $this->iris()->inspect("trail:{$trail->id}:"));

        app(TrailService::class)->delete($trail);
        $this->assertSame([], $this->iris()->inspect("trail:{$trail->id}:"));
    }

    public function testExportCarriesSuggestionsAndTrailContext(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);
        TrailItem::factory()->create([
            'research_trail_id' => $trail->id,
            'artwork_id' => Artwork::factory()->create()->id,
            'position' => 1,
        ]);
        app(TrailMemoryService::class)->semanticSummary($trail);

        Recommendation::factory()->create([
            'user_id' => $user->id,
            'research_trail_id' => $trail->id,
            'artwork_id' => Artwork::factory()->create(['title' => 'Suggested Work'])->id,
            'reason' => 'Also by the same hand',
        ]);

        $export = app(MemoryExportService::class)->export($user);

        $this->assertCount(1, $export['suggestions']);
        $this->assertSame('Suggested Work', $export['suggestions'][0]['artwork_title']);
        $this->assertSame('Also by the same hand', $export['suggestions'][0]['reason']);
        $this->assertSame('shown', $export['suggestions'][0]['status']);
        $this->assertNotEmpty($export['suggestions'][0]['citations']);

        $this->assertArrayHasKey("trail:{$trail->id}:semantic_summary", $export['held_context']);
    }

    public function testDeletionRemovesSuggestionsAndPurgesTrailNamespaces(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);
        TrailItem::factory()->create([
            'research_trail_id' => $trail->id,
            'artwork_id' => Artwork::factory()->create()->id,
            'position' => 1,
        ]);
        app(TrailMemoryService::class)->semanticSummary($trail);

        Recommendation::factory()
            ->count(2)
            ->create(['user_id' => $user->id, 'research_trail_id' => $trail->id]);

        $counts = app(MemoryDeletionService::class)->deleteAll($user);

        $this->assertSame(2, $counts['suggestions']);
        $this->assertSame(0, $user->recommendations()->count());
        $this->assertSame([], $this->iris()->inspect("user:{$user->id}:"));
        $this->assertSame([], $this->iris()->inspect("trail:{$trail->id}:"));

        // The receipt names the suggestions too.
        $receipt = $user->memoryEvents()->where('event_type', MemoryEvent::MEMORY_DELETED)->sole();
        $this->assertStringContainsString('2 suggestions', $receipt->description());
    }
}
