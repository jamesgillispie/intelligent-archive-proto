<?php

namespace Tests\Feature\Trails;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\UserNote;
use App\Models\Memory\MemoryEvent;
use App\Models\Memory\ResearchTrail;
use App\Services\Memory\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrailNotesTest extends TestCase
{
    use RefreshDatabase;

    private function consentedUser(): User
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        return $user;
    }

    public function testWritingANoteOnATrailIsLedgered(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id, 'title' => 'Dutch Interiors']);

        $this->actingAs($user)
            ->post(route('account.trails.notes.store', $trail->id), ['body' => 'Light from the left, always.'])
            ->assertRedirect(route('account.trails.show', $trail->id));

        $note = $user->notes()->sole();
        $this->assertSame('Light from the left, always.', $note->body);
        $this->assertTrue($note->notable->is($trail));

        $event = $user->memoryEvents()->where('event_type', MemoryEvent::NOTE_WRITTEN)->sole();
        $this->assertSame('Dutch Interiors', $event->payload['trail_title']);
    }

    public function testNotesShowInTheWorkspace(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);
        UserNote::factory()->create([
            'user_id' => $user->id,
            'notable_id' => $trail->id,
            'body' => 'Light from the left, always.',
        ]);

        $this->actingAs($user)
            ->get(route('account.trails.show', $trail->id))
            ->assertOk()
            ->assertSee('Light from the left, always.');
    }

    public function testYouCannotNoteSomeoneElsesTrail(): void
    {
        $trail = ResearchTrail::factory()->create();

        $this->actingAs($this->consentedUser())
            ->post(route('account.trails.notes.store', $trail->id), ['body' => 'Trespassing.'])
            ->assertNotFound();

        $this->assertSame(0, UserNote::count());
    }

    public function testDeletingANoteIsAlwaysAvailable(): void
    {
        $user = $this->consentedUser();
        $trail = ResearchTrail::factory()->create(['user_id' => $user->id]);
        $note = UserNote::factory()->create(['user_id' => $user->id, 'notable_id' => $trail->id]);

        // Paused memory blocks new notes but never deletion.
        $user->personalizationSetting->update(['memory_paused' => true]);

        $this->actingAs($user)
            ->post(route('account.trails.notes.store', $trail->id), ['body' => 'Blocked.'])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('account.notes.destroy', $note->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('user_notes', ['id' => $note->id]);
        $this->assertSame(1, $user->memoryEvents()->where('event_type', MemoryEvent::NOTE_DELETED)->count());
    }

    public function testOthersCannotDeleteYourNote(): void
    {
        $note = UserNote::factory()->create();

        $this->actingAs($this->consentedUser())
            ->delete(route('account.notes.destroy', $note->id))
            ->assertNotFound();

        $this->assertDatabaseHas('user_notes', ['id' => $note->id]);
    }
}
