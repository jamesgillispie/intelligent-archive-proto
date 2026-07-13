<?php

namespace Tests\Feature\Memory;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\MemoryConsent;
use App\Models\Memory\ResearchTrail;
use App\Services\Trails\TrailService;
use App\Services\Memory\ConsentService;
use App\Services\Memory\TrailMemoryService;
use App\Services\Memory\VisitorContextService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Flow B via Iris: the active-trail pointer is written only by explicit,
 * ledgered actions, verified against SQL on every read, and rebuilt from
 * SQL on any miss. Consent gates every write; pause purges.
 */
class TrailMemoryTest extends TestCase
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

    private function heldTrailId(User $user): mixed
    {
        $held = $this->iris()->get("user:{$user->id}:active_trail");
        $this->assertNotNull($held, 'Expected an active-trail pointer to be held');

        return $held['trail_id'];
    }

    public function testStartingATrailWarmsTheActiveTrailPointer(): void
    {
        $user = $this->consentedUser();

        $this->actingAs($user)->post(route('account.trails.store'), ['title' => 'Dutch Interiors']);

        $trail = $user->trails()->sole();
        $held = $this->iris()->get("user:{$user->id}:active_trail");

        $this->assertNotNull($held);
        $this->assertSame($trail->id, $held['trail_id']);
        $this->assertSame('Dutch Interiors', $held['trail_title']);
    }

    public function testTheContinuePanelFollowsTheIrisPointerNotRecency(): void
    {
        $user = $this->consentedUser();
        $pointed = ResearchTrail::factory()->create([
            'user_id' => $user->id,
            'title' => 'Dutch Interiors',
            'last_activity_at' => now()->subDays(2),
        ]);
        ResearchTrail::factory()->create([
            'user_id' => $user->id,
            'title' => 'Hudson River Light',
            'last_activity_at' => now(),
        ]);

        app(TrailMemoryService::class)->refreshActiveTrail($pointed);

        // SQL recency says Hudson River Light; the pointer says Dutch
        // Interiors — and the pointer is what "continue" means.
        $this->actingAs($user)
            ->get(route('account.trails'))
            ->assertOk()
            ->assertSeeInOrder(['Continue where you left off', 'Dutch Interiors', 'Active trails']);
    }

    public function testAStalePointerFallsBackToSqlAndRebuilds(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id, 'last_activity_at' => now()]);

        $this->iris()->put("user:{$user->id}:active_trail", ['trail_id' => 999999]);

        $resolved = app(TrailMemoryService::class)->activeTrail($user);

        $this->assertNotNull($resolved);
        $this->assertSame($trail->id, $resolved->id);
        // The miss re-warmed the pointer from SQL truth.
        $this->assertSame($trail->id, $this->heldTrailId($user));
    }

    public function testNothingIsWrittenWhilePausedOrWithStaleConsent(): void
    {
        $paused = $this->consentedUser();
        $paused->personalizationSetting->update(['memory_paused' => true]);

        app(TrailService::class)->start($paused, 'Quiet Work');

        $this->assertNull($this->iris()->get("user:{$paused->id}:active_trail"));

        // Reading while paused stays SQL-only: the trail resolves, Iris
        // stays empty.
        $resolved = app(TrailMemoryService::class)->activeTrail($paused);
        $this->assertSame('Quiet Work', $resolved->title);
        $this->assertNull($this->iris()->get("user:{$paused->id}:active_trail"));

        $stale = User::factory()->create();
        MemoryConsent::factory()
            ->stale()
            ->create(['user_id' => $stale->id]);

        app(TrailService::class)->start($stale, 'Stale Consent Work');

        $this->assertNull($this->iris()->get("user:{$stale->id}:active_trail"));
    }

    public function testArchivingOrDeletingThePointedTrailDropsThePointer(): void
    {
        $user = $this->consentedUser();
        $trails = app(TrailService::class);

        $first = $trails->start($user, 'First');
        $second = $trails->start($user, 'Second');

        $this->assertSame($second->id, $this->heldTrailId($user));

        // Deleting a trail the pointer is not at leaves it alone.
        $trails->delete($first);
        $this->assertSame($second->id, $this->heldTrailId($user));

        // Archiving the pointed trail drops it; reactivation re-warms it.
        $trails->archive($second);
        $this->assertNull($this->iris()->get("user:{$user->id}:active_trail"));

        $trails->reactivate($second);
        $this->assertSame($second->id, $this->heldTrailId($user));
    }

    public function testPausePurgesHeldContextAndResumeRebuildsLazily(): void
    {
        $user = $this->consentedUser();
        app(TrailService::class)->start($user, 'Dutch Interiors');

        $this->assertNotSame([], $this->iris()->inspect("user:{$user->id}:"));

        $this->actingAs($user)->post(route('account.memory.pause'));

        $this->assertSame([], $this->iris()->inspect("user:{$user->id}:"));

        // Resume does not eagerly rebuild — context returns on next use.
        $this->actingAs($user)->post(route('account.memory.resume'));
        $this->assertSame([], $this->iris()->inspect("user:{$user->id}:"));

        $this->actingAs($user)->get(route('account.trails'))->assertOk();
        $this->assertNotNull($this->iris()->get("user:{$user->id}:active_trail"));
    }

    public function testADeliberateRevisitRefreshesThePointer(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create([
            'user_id' => $user->id,
            'last_activity_at' => now()->subHours(3),
        ]);

        $this->actingAs($user)
            ->get(route('account.trails.show', $trail->id))
            ->assertOk();

        $this->assertSame($trail->id, $this->heldTrailId($user));
    }

    public function testAnonymousSessionContextIsIsolatedFromUserPrefixes(): void
    {
        $visitors = app(VisitorContextService::class);

        $this->iris()->put($visitors->sessionNamespace('abc', 'anonymous_context'), ['intent' => 'storms']);

        $this->assertSame([], $this->iris()->inspect('user:'));
        $this->assertNotNull($this->iris()->get('session:abc:anonymous_context'));
    }
}
