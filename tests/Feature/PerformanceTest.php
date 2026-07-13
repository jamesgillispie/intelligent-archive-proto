<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\TrailItem;
use Illuminate\Support\Facades\DB;
use App\Models\Memory\ResearchTrail;
use App\Services\Memory\ConsentService;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * The performance contract of the hot paths: query counts stay flat as a
 * trail grows (no per-item lazy loads), and the public cache covers exactly
 * the anonymous archive surfaces — never anything sessioned or consented.
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTrail(User $user, int $itemCount): ResearchTrail
    {
        $artist = Artist::factory()->create();

        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);

        for ($position = 1; $position <= $itemCount; $position++) {
            $work = Artwork::factory()->withPrimaryImage()->create();
            $work->artists()->attach($artist->id, ['role' => 'artist']);

            TrailItem::factory()->create([
                'research_trail_id' => $trail->id,
                'artwork_id' => $work->id,
                'position' => $position,
            ]);
        }

        return $trail;
    }

    private function countQueries(callable $request): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $request();

        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    }

    public function testTrailPageQueriesStayFlatAsTheTrailGrows(): void
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        $small = $this->makeTrail($user, 3);
        $large = $this->makeTrail($user, 9);

        // First visit records the revisit event; measure the steady state.
        $this->actingAs($user)
            ->get(route('account.trails.show', $small->id))
            ->assertOk();
        $this->actingAs($user)
            ->get(route('account.trails.show', $large->id))
            ->assertOk();

        $forSmall = $this->countQueries(
            fn() => $this->actingAs($user)
                ->get(route('account.trails.show', $small->id))
                ->assertOk(),
        );
        $forLarge = $this->countQueries(
            fn() => $this->actingAs($user)
                ->get(route('account.trails.show', $large->id))
                ->assertOk(),
        );

        $this->assertSame($forSmall, $forLarge, 'Trail page queries must not grow with item count.');
    }

    public function testComparePageQueriesStayFlatAsMoreWorksAreCompared(): void
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        $trail = $this->makeTrail($user, 4);
        $ids = $trail->items()->orderBy('position')->pluck('artwork_id')->values();

        $urlFor = fn(int $count) => route('archive.compare', [
            'works' => $ids->take($count)->all(),
            'trail' => $trail->id,
        ]);

        // First visits write the comparison_requested ledger rows; measure
        // the steady state within the dedupe window.
        $this->actingAs($user)->get($urlFor(2))->assertOk();
        $this->actingAs($user)->get($urlFor(4))->assertOk();

        $forTwo = $this->countQueries(fn() => $this->actingAs($user)->get($urlFor(2))->assertOk());
        $forFour = $this->countQueries(fn() => $this->actingAs($user)->get($urlFor(4))->assertOk());

        $this->assertSame($forTwo, $forFour, 'Compare page queries must not grow with the number of works.');
    }

    public function testThePublicCacheCoversExactlyTheAnonymousArchiveSurfaces(): void
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        foreach (['archive.index', 'archive.artworks.index', 'archive.artists.index', 'methodology'] as $cached) {
            $header = (string) $this->get(route($cached))->assertOk()->headers->get('Cache-Control');

            $this->assertStringContainsString('public', $header, "{$cached} should be publicly cacheable");
            $this->assertStringContainsString('s-maxage=3600', $header, "{$cached} should be publicly cacheable");
        }

        // Sessioned or consented surfaces must never reach a shared cache.
        $uncached = [
            $this->get(route('archive.explore'))->headers->get('Cache-Control'),
            $this->actingAs($user)->get(route('account.memory'))->headers->get('Cache-Control'),
            $this->actingAs($user)->get(route('account.trails'))->headers->get('Cache-Control'),
        ];

        foreach ($uncached as $header) {
            $this->assertStringNotContainsString('s-maxage', (string) $header);
            $this->assertStringNotContainsString('public', (string) $header);
        }
    }
}
