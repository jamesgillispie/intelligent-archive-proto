<?php

namespace Tests\Feature\Nexus;

use Tests\TestCase;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * First Nexus surface (STRATEGY §14): the non-personalized related-works
 * panel on artwork detail, every entry explained and cited.
 */
class RelatedWorksPanelTest extends TestCase
{
    use RefreshDatabase;

    private function slugFor(Artwork $artwork): string
    {
        return (string) $artwork->slugs()->where('active', true)->value('slug');
    }

    public function testTheArtworkPageShowsRelatedWorksWithReasonsAndCitations(): void
    {
        $artist = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);

        $subject = Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'The Oxbow View', 'source_title' => 'The Oxbow View']);
        $related = Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'Autumn on the Hudson', 'source_title' => 'Autumn on the Hudson']);

        $subject->artists()->attach($artist->id, ['role' => 'painter']);
        $related->artists()->attach($artist->id, ['role' => 'painter']);

        $this->get(route('archive.artworks.show', $this->slugFor($subject)))
            ->assertOk()
            ->assertSee('Related in the collection')
            ->assertSee('Autumn on the Hudson')
            ->assertSee('Also by Thomas Cole')
            ->assertSee('Why this?')
            // The transparency line: relationships come from the record,
            // never from visitor activity (the page is publicly cached).
            ->assertSee('never from your activity');
    }

    public function testUndisplayableWorksNeverAppearInThePanel(): void
    {
        $artist = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);

        $subject = Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'The Oxbow View', 'source_title' => 'The Oxbow View']);
        $hidden = Artwork::factory()->create([
            'title' => 'Hidden Companion Piece',
            'source_title' => 'Hidden Companion Piece',
            'is_displayable' => false,
        ]);

        $subject->artists()->attach($artist->id, ['role' => 'painter']);
        $hidden->artists()->attach($artist->id, ['role' => 'painter']);

        $this->get(route('archive.artworks.show', $this->slugFor($subject)))
            ->assertOk()
            ->assertDontSee('Hidden Companion Piece');
    }
}
