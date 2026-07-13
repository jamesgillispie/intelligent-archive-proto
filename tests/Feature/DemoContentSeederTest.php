<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use RuntimeException;
use App\Services\Memory\ConsentService;
use Database\Seeders\DemoContentSeeder;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Twill\Capsules\CuratedSets\Models\CuratedSet;

class DemoContentSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedArchive(): void
    {
        $artist = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);

        Artwork::factory()
            ->withPrimaryImage()
            ->count(9)
            ->create()
            ->take(4)
            ->each(fn(Artwork $work) => $work->artists()->attach($artist->id, ['role' => 'artist']));
    }

    public function testItSeedsAConsentedVisitorWithGenuineMemoryAndACuratedSet(): void
    {
        $this->seedArchive();

        $this->seed(DemoContentSeeder::class);

        $user = User::where('email', DemoContentSeeder::DEMO_EMAIL)->sole();

        // Consent granted through the real boundary.
        $this->assertTrue(app(ConsentService::class)->hasCurrentConsent($user));

        // A lived-in trail anchored on the artist with the most works, and
        // a just-started one.
        $this->assertSame(2, $user->trails()->count());

        $rich = $user->trails()->where('title', 'Following Thomas Cole')->sole();
        $this->assertSame(4, $rich->items()->count());
        $this->assertSame(1, $rich->notes()->count());

        // The ledger is genuine: started twice, saved five times, one note.
        $this->assertSame(2, $user->memoryEvents()->where('event_type', 'trail_started')->count());
        $this->assertSame(5, $user->memoryEvents()->where('event_type', 'artwork_saved_to_trail')->count());
        $this->assertSame(1, $user->memoryEvents()->where('event_type', 'note_written')->count());

        $set = CuratedSet::where('title', 'Ways of seeing weather')->where('published', true)->sole();
        $this->assertSame(8, $set->artworks()->count());
    }

    public function testReseedingChangesNothing(): void
    {
        $this->seedArchive();

        $this->seed(DemoContentSeeder::class);
        $this->seed(DemoContentSeeder::class);

        $user = User::where('email', DemoContentSeeder::DEMO_EMAIL)->sole();

        $this->assertSame(2, $user->trails()->count());
        $this->assertSame(1, CuratedSet::count());
    }

    public function testItFailsLoudlyOnAnEmptyArchive(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('at least 8 displayable works');

        $this->seed(DemoContentSeeder::class);
    }

    public function testItRefusesToRunInProduction(): void
    {
        $this->seedArchive();

        $this->app['env'] = 'production';

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('never belongs in production');

            // Called directly: `db:seed` would show its own production
            // confirmation prompt before the seeder's guard could fire.
            app(DemoContentSeeder::class)->run();
        } finally {
            $this->app['env'] = 'testing';
        }
    }
}
