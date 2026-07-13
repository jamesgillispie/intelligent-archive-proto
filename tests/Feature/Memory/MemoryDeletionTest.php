<?php

namespace Tests\Feature\Memory;

use Tests\TestCase;
use App\Models\User;
use RuntimeException;
use App\Models\Memory\TrailItem;
use App\Models\Memory\ArtworkSave;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\MemoryConsent;
use App\Models\Memory\ResearchTrail;
use App\Services\Trails\TrailService;
use App\Services\Memory\ConsentService;
use App\Services\Memory\MemoryDeletionService;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Flow F: whole-memory deletion with a minimal receipt, Iris purge and
 * read-back verification. Deletion is a right that survives pause and
 * stale consent, and it is always scoped to the requesting owner.
 */
class MemoryDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function consentedUser(): User
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        return $user;
    }

    private function userWithMemory(): User
    {
        $user = $this->consentedUser();
        $trail = app(TrailService::class)->start($user, 'Dutch Interiors', null, Artwork::factory()->create());
        $trail->notes()->create(['user_id' => $user->id, 'body' => 'A note.']);
        ArtworkSave::factory()->create(['user_id' => $user->id]);

        return $user;
    }

    public function testTheConfirmationPageStatesExactlyWhatDisappears(): void
    {
        $user = $this->userWithMemory();

        $this->actingAs($user)
            ->get(route('account.memory.delete'))
            ->assertOk()
            ->assertSee('Delete everything?')
            ->assertSee('1 trail')
            ->assertSee('holding 1 saved work')
            ->assertSee('1 save outside trails')
            ->assertSee('1 note');
    }

    public function testDeletionRemovesEverythingLeavesAReceiptAndPurgesIris(): void
    {
        $user = $this->userWithMemory();
        $eventsBefore = $user->memoryEvents()->count();

        $this->assertNotSame([], app(RedisIrisMemoryContract::class)->inspect("user:{$user->id}:"));

        $this->actingAs($user)->delete(route('account.memory.destroy'))->assertRedirect(route('account.memory'));

        $this->assertSame(0, $user->trails()->count());
        $this->assertSame(0, TrailItem::count());
        $this->assertSame(0, $user->artworkSaves()->count());
        $this->assertSame(0, $user->notes()->count());

        $receipt = $user->memoryEvents()->sole();
        $this->assertSame(MemoryEvent::MEMORY_DELETED, $receipt->event_type);
        $this->assertSame(1, $receipt->payload['trails']);
        $this->assertSame(1, $receipt->payload['works']);
        $this->assertSame(1, $receipt->payload['saves']);
        $this->assertSame(1, $receipt->payload['notes']);
        $this->assertSame($eventsBefore, $receipt->payload['entries']);
        $this->assertStringContainsString('1 trail', $receipt->description());

        $this->assertSame([], app(RedisIrisMemoryContract::class)->inspect("user:{$user->id}:"));

        // The receipt is itself deletable, like any ledger row.
        $this->actingAs($user)->delete(route('account.memory.events.destroy', $receipt->id));
        $this->assertSame(0, $user->memoryEvents()->count());
    }

    public function testDeletionWorksWhilePausedAndWithStaleConsent(): void
    {
        $paused = $this->userWithMemory();
        $paused->personalizationSetting->update(['memory_paused' => true]);

        $this->actingAs($paused)->get(route('account.memory.delete'))->assertOk();
        $this->actingAs($paused)->delete(route('account.memory.destroy'))->assertRedirect();
        $this->assertSame(0, $paused->trails()->count());

        $stale = User::factory()->create();
        MemoryConsent::factory()
            ->stale()
            ->create(['user_id' => $stale->id]);
        ResearchTrail::factory()->create(['user_id' => $stale->id]);

        $this->actingAs($stale)->delete(route('account.memory.destroy'))->assertRedirect();
        $this->assertSame(0, $stale->trails()->count());
    }

    public function testDeletionIsScopedToTheOwner(): void
    {
        $bystander = $this->userWithMemory();
        $deleter = $this->userWithMemory();

        $this->actingAs($deleter)->delete(route('account.memory.destroy'));

        $this->assertSame(1, $bystander->trails()->count());
        $this->assertSame(1, $bystander->artworkSaves()->count());
        $this->assertNotSame(
            [],
            app(RedisIrisMemoryContract::class)->inspect("user:{$bystander->id}:"),
            'A neighbor’s held context must survive someone else’s deletion',
        );
    }

    public function testAPurgeThatCannotProveItselfFailsLoudly(): void
    {
        $user = $this->userWithMemory();

        // An Iris whose purge does nothing: read-back verification must
        // refuse to call the deletion done.
        $this->app->instance(
            RedisIrisMemoryContract::class,
            new class implements RedisIrisMemoryContract {
                public function put(string $namespace, array $context, ?int $ttlSeconds = null): void {}

                public function get(string $namespace): ?array
                {
                    return null;
                }

                public function forget(string $namespace): void {}

                public function inspect(string $prefix): array
                {
                    return ["{$prefix}active_trail" => ['trail_id' => 1]];
                }

                public function purge(string $prefix): void {}
            },
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('purge verification failed');

        app(MemoryDeletionService::class)->deleteAll($user);
    }
}
