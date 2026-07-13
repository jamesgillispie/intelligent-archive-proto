<?php

namespace Tests\Feature\Memory;

use Tests\TestCase;
use App\Models\User;
use App\Models\Memory\MemoryConsent;
use App\Services\Memory\NoteService;
use App\Services\Trails\TrailService;
use App\Services\Memory\ConsentService;
use App\Services\Memory\ArtworkSaveService;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemoryExportTest extends TestCase
{
    use RefreshDatabase;

    private function consentedUser(): User
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        return $user;
    }

    public function testTheExportContainsTheWholeRecord(): void
    {
        $user = $this->consentedUser();
        $skater = Artwork::factory()->create(['title' => 'The Skater']);
        $oxbow = Artwork::factory()->create(['title' => 'The Oxbow']);

        $trail = app(TrailService::class)->start(
            $user,
            'Dutch Interiors',
            'Who lit these rooms?',
            $skater,
            'Start here.',
        );
        app(NoteService::class)->write($user, $trail, 'Light from the left, always.');
        app(ArtworkSaveService::class)->save($user, $oxbow);

        $this->actingAs($user)
            ->get(route('account.export'))
            ->assertOk()
            ->assertSee('Download as JSON')
            ->assertSee('Dutch Interiors');

        $response = $this->actingAs($user)->get(route('account.export.download'));
        $response->assertOk();

        $this->assertStringStartsWith(
            'attachment; filename="memory-export-',
            (string) $response->headers->get('Content-Disposition'),
        );

        $export = $response->json();

        $this->assertSame(config('memory.consent_version'), $export['agreements'][0]['consent_version']);
        $this->assertFalse($export['memory_switches']['memory_paused']);

        $this->assertSame('Dutch Interiors', $export['trails'][0]['title']);
        $this->assertSame('Who lit these rooms?', $export['trails'][0]['intent_note']);
        $this->assertSame('The Skater', $export['trails'][0]['works'][0]['artwork_title']);
        $this->assertSame('Start here.', $export['trails'][0]['works'][0]['note']);
        $this->assertSame('Light from the left, always.', $export['trails'][0]['notes'][0]['body']);

        $this->assertSame('The Oxbow', $export['saves'][0]['artwork_title']);

        $events = array_column($export['ledger'], 'event');
        $this->assertContains('trail_started', $events);
        $this->assertContains('artwork_saved_to_trail', $events);
        $this->assertContains('note_written', $events);
        $this->assertContains('artwork_saved', $events);

        // The held Iris context ships verbatim.
        $this->assertSame($trail->id, $export['held_context']["user:{$user->id}:active_trail"]['trail_id']);
    }

    public function testExportRemainsAvailableWhilePausedAndWithStaleConsent(): void
    {
        $paused = $this->consentedUser();
        $paused->personalizationSetting->update(['memory_paused' => true]);

        $response = $this->actingAs($paused)->get(route('account.export.download'));
        $response->assertOk();
        $this->assertTrue($response->json('memory_switches.memory_paused'));

        $stale = User::factory()->create();
        MemoryConsent::factory()
            ->stale()
            ->create(['user_id' => $stale->id]);

        $this->actingAs($stale)->get(route('account.export'))->assertOk();
        $this->actingAs($stale)->get(route('account.export.download'))->assertOk();
    }

    public function testTheExportPageIsNeverPubliclyCacheable(): void
    {
        $response = $this->actingAs($this->consentedUser())->get(route('account.export'));

        $this->assertStringNotContainsString('public', (string) $response->headers->get('Cache-Control'));
    }
}
