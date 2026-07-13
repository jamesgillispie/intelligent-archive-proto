<?php

namespace Tests\Feature\Archive;

use Tests\TestCase;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Twill\Capsules\CuratedSets\Models\CuratedSet;

/**
 * Curated sets are the editorial lever on the archive landing — published
 * sets render their works in editorial order; anything unpublished or
 * hollow renders nothing at all.
 */
class CuratedSetTest extends TestCase
{
    use RefreshDatabase;

    public function testAPublishedSetRendersItsWorksInEditorialOrder(): void
    {
        $first = Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'Morning on the River', 'source_title' => 'Morning on the River']);
        $second = Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'The Quiet Valley', 'source_title' => 'The Quiet Valley']);

        $set = CuratedSet::create([
            'published' => true,
            'title' => 'American light',
            'description' => 'Works that share a way of seeing weather.',
        ]);
        $set->artworks()->attach([$second->id => ['position' => 1], $first->id => ['position' => 2]]);

        $response = $this->get(route('archive.index'))
            ->assertOk()
            ->assertSee('American light')
            ->assertSee('Works that share a way of seeing weather.')
            ->assertSee('The Quiet Valley')
            ->assertSee('Morning on the River');

        // Editorial pivot order, not id order.
        $content = $response->getContent();
        $this->assertLessThan(strpos($content, 'Morning on the River'), strpos($content, 'The Quiet Valley'));
    }

    public function testUnpublishedAndHollowSetsRenderNothing(): void
    {
        $displayable = Artwork::factory()->withPrimaryImage()->create();

        $unpublished = CuratedSet::create(['published' => false, 'title' => 'Still in progress']);
        $unpublished->artworks()->attach([$displayable->id => ['position' => 1]]);

        // Published, but every work in it is unpublished — no hollow section.
        $hidden = Artwork::factory()->create(['published' => false]);
        $hollow = CuratedSet::create(['published' => true, 'title' => 'Nothing to show']);
        $hollow->artworks()->attach([$hidden->id => ['position' => 1]]);

        $this->get(route('archive.index'))
            ->assertOk()
            ->assertDontSee('Still in progress')
            ->assertDontSee('Nothing to show');
    }
}
