<?php

namespace Database\Seeders;

use App\Models\User;
use RuntimeException;
use Illuminate\Database\Seeder;
use App\Services\Memory\NoteService;
use App\Services\Trails\TrailService;
use App\Services\Memory\ConsentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Twill\Capsules\CuratedSets\Models\CuratedSet;

/**
 * Demo content for the STRATEGY §12 smallest demo: a consented visitor with
 * one lived-in trail, one just-started trail, and a curated set on the
 * archive landing. Everything goes through the real services — the ledger,
 * item origins and held context are genuine, not staged — so what the demo
 * shows is what the system actually does.
 *
 * Idempotent: re-running skips anything that already exists. Never runs in
 * production.
 */
class DemoContentSeeder extends Seeder
{
    public const DEMO_EMAIL = 'demo@trails.test';

    public function __construct(
        private readonly ConsentService $consent,
        private readonly TrailService $trails,
        private readonly NoteService $notes,
    ) {}

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Demo content never belongs in production.');
        }

        // db:seed runs unguarded, which makes Twill's publishedInListings
        // scope believe a `public` column is fillable and query it. All
        // writes below go through fillable attributes anyway.
        Model::reguard();

        $works = Artwork::displayable()->with('artists')->orderBy('id')->get();

        if ($works->count() < 8) {
            throw new RuntimeException(
                'The demo needs at least 8 displayable works — import the archive subset first (nga:import).',
            );
        }

        $this->curatedSet($works);
        $this->demoVisitor($works);
    }

    /**
     * @param Collection<int, Artwork> $works
     */
    protected function curatedSet(Collection $works): void
    {
        if (CuratedSet::where('title', 'Ways of seeing weather')->exists()) {
            return;
        }

        $set = CuratedSet::create([
            'published' => true,
            'title' => 'Ways of seeing weather',
            'description' => 'Eight works that hold light, storm and season still long enough to look at them.',
        ]);

        $set->artworks()->attach(
            $works
                ->take(8)
                ->values()
                ->mapWithKeys(
                    fn(Artwork $work, int $index) => [
                        $work->id => ['position' => $index + 1],
                    ],
                )
                ->all(),
        );
    }

    /**
     * @param Collection<int, Artwork> $works
     */
    protected function demoVisitor(Collection $works): void
    {
        if (User::where('email', self::DEMO_EMAIL)->exists()) {
            return;
        }

        $user = User::factory()->create([
            'name' => 'Demo Visitor',
            'email' => self::DEMO_EMAIL,
        ]);

        // Registration is the consent grant — same boundary as the real flow.
        $this->consent->grant($user);

        [$anchorWorks, $anchorArtist] = $this->anchorWorks($works);

        $trail = $this->trails->start(
            $user,
            $anchorArtist === null ? 'A first thread' : "Following {$anchorArtist->name}",
            'What holds these works together — the hand, the weather, or the way of looking?',
        );

        foreach ($anchorWorks as $index => $work) {
            $this->trails->addArtwork($trail, $work, $index === 0 ? 'The starting point.' : null);
        }

        $this->notes->write(
            $user,
            $trail,
            'Return to the horizons — they sit low in every one of these, and the sky does the arguing.',
        );

        // A second, just-started trail so the trails page shows both states.
        $spare = $works->reject(fn(Artwork $work) => $anchorWorks->contains('id', $work->id))->first();

        if ($spare !== null) {
            $this->trails->start($user, 'A second look', null, $spare);
        }
    }

    /**
     * The richest expansion demo needs works that share an anchor — prefer
     * the artist with the most displayable works, fall back to the first
     * four works if no artist has enough.
     *
     * @param Collection<int, Artwork> $works
     * @return array{0: Collection<int, Artwork>, 1: Artist|null}
     */
    protected function anchorWorks(Collection $works): array
    {
        $byArtist = [];

        foreach ($works as $work) {
            foreach ($work->artists as $artist) {
                $byArtist[$artist->id][] = $work;
            }
        }

        uasort($byArtist, fn(array $a, array $b) => count($b) <=> count($a));

        foreach ($byArtist as $artistId => $artistWorks) {
            if (count($artistWorks) >= 3) {
                /** @var Artist $artist */
                $artist = Artist::query()->find($artistId);

                return [Collection::make($artistWorks)->take(4)->values(), $artist];
            }
        }

        return [$works->take(4)->values(), null];
    }
}
