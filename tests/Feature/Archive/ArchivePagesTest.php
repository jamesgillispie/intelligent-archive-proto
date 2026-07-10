<?php

namespace Tests\Feature\Archive;

use Tests\TestCase;
use A17\Twill\Models\Media;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Twill\Capsules\Artworks\Models\ImageAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Twill\Capsules\Classifications\Models\Classification;

class ArchivePagesTest extends TestCase
{
    use RefreshDatabase;

    public function testArchiveLandingRendersWithCounts(): void
    {
        Artwork::factory()->withPrimaryImage()->count(2)->create();

        $this->get(route('archive.index'))->assertOk()->assertSee('The Archive')->assertSee('Browse artworks');
    }

    public function testArtworksIndexListsDisplayableWorksOnly(): void
    {
        $visible = Artwork::factory()
            ->withPrimaryImage()
            ->create(['title' => 'Visible Work', 'source_title' => 'Visible Work']);
        Artwork::factory()->create([
            'title' => 'Hidden Work',
            'source_title' => 'Hidden Work',
            'is_displayable' => false,
        ]);

        $this->get(route('archive.artworks.index'))
            ->assertOk()
            ->assertSee('Visible Work')
            ->assertDontSee('Hidden Work');
    }

    public function testArtworksIndexFiltersByClassificationAndKeyword(): void
    {
        $painting = Classification::create(['published' => true, 'name' => 'Painting', 'source_name' => 'Painting']);
        $sculpture = Classification::create(['published' => true, 'name' => 'Sculpture', 'source_name' => 'Sculpture']);

        Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'River Landscape',
                'source_title' => 'River Landscape',
                'classification_id' => $painting->id,
            ]);
        Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'Bronze Figure',
                'source_title' => 'Bronze Figure',
                'classification_id' => $sculpture->id,
            ]);

        $this->get(route('archive.artworks.index', ['classification' => $painting->id]))
            ->assertOk()
            ->assertSee('River Landscape')
            ->assertDontSee('Bronze Figure');

        $this->get(route('archive.artworks.index', ['q' => 'bronze']))
            ->assertOk()
            ->assertSee('Bronze Figure')
            ->assertDontSee('River Landscape');

        $this->get(route('archive.artworks.index', ['classification' => 999999]))->assertSessionHasErrors(
            'classification',
        );
    }

    public function testArtworkDetailShowsMetadataAndSourceCitation(): void
    {
        $artwork = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'The Oxbow View',
                'source_title' => 'The Oxbow View',
                'nga_object_id' => '424242',
            ]);

        $slug = $artwork->slugs()->where('active', true)->value('slug');

        $this->get(route('archive.artworks.show', $slug))
            ->assertOk()
            ->assertSee('The Oxbow View')
            ->assertSee('National Gallery of Art Open Data')
            ->assertSee('424242')
            ->assertSee('Open access image');
    }

    public function testArtworkDetailNamesEditorialOverridesInTheCitation(): void
    {
        $artwork = Artwork::factory()
            ->withPrimaryImage()
            ->create([
                'title' => 'Imported Title',
                'source_title' => 'Imported Title',
            ]);
        $artwork->update(['title' => 'Refined Editorial Title']);

        $slug = $artwork->slugs()->where('active', true)->value('slug');

        $this->get(route('archive.artworks.show', $slug))
            ->assertOk()
            ->assertSee('Refined Editorial Title')
            ->assertSee('refined editorially')
            ->assertSee('Imported Title');
    }

    public function testUndisplayableArtworkIs404OnTheFront(): void
    {
        $artwork = Artwork::factory()->create(['is_displayable' => false]);
        $slug = $artwork->slugs()->where('active', true)->value('slug');

        $this->get(route('archive.artworks.show', $slug))->assertNotFound();
    }

    public function testDownloadedArtworksRenderLocalMediaThroughGlide(): void
    {
        $artwork = Artwork::factory()
            ->withPrimaryImage(['nga_uuid' => 'img-local'])
            ->create();

        $media = Media::create([
            'uuid' => 'img-local/img-local.jpg',
            'filename' => 'img-local.jpg',
            'width' => 800,
            'height' => 1000,
            'alt_text' => 'A downloaded painting',
        ]);
        $artwork->medias()->attach($media->id, [
            'role' => Artwork::IMAGE_ROLE,
            'crop' => 'default',
            'ratio' => 'default',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 800,
            'crop_h' => 1000,
            'metadatas' => '{}',
        ]);
        $artwork
            ->images()
            ->firstOrFail()
            ->forceFill(['media_id' => $media->id])
            ->save();

        $slug = $artwork->slugs()->where('active', true)->value('slug');
        $response = $this->get(route('archive.artworks.show', $slug))->assertOk();

        // Served locally via Glide, not from the NGA IIIF endpoint.
        $response->assertSee('/img/img-local/img-local.jpg', false);
        $response->assertSee('A downloaded painting');
        $response->assertDontSee('api.nga.gov/iiif/img-local', false);
    }

    public function testPendingDownloadsFallBackToRemoteIiif(): void
    {
        $artwork = Artwork::factory()->withPrimaryImage()->create();
        $iiifUrl = $artwork->images()->firstOrFail()->iiif_url;

        $slug = $artwork->slugs()->where('active', true)->value('slug');

        $this->get(route('archive.artworks.show', $slug))->assertOk()->assertSee($iiifUrl, false);
    }

    public function testFailedDownloadsRenderAQuietPlaceholder(): void
    {
        $artwork = Artwork::factory()
            ->withPrimaryImage([
                'validation_status' => ImageAsset::VALIDATION_FAILED,
                'failure_reason' => 'http_404',
            ])
            ->create();
        $iiifUrl = $artwork->images()->firstOrFail()->iiif_url;

        $slug = $artwork->slugs()->where('active', true)->value('slug');

        $this->get(route('archive.artworks.show', $slug))
            ->assertOk()
            ->assertSee('No open-access image is available for this work.')
            ->assertDontSee($iiifUrl, false);
    }

    public function testArtistPagesRender(): void
    {
        $artist = Artist::factory()->create(['name' => 'Thomas Cole', 'source_name' => 'Thomas Cole']);
        $artwork = Artwork::factory()->withPrimaryImage()->create();
        $artwork->artists()->attach($artist->id, ['role' => 'artist']);

        $this->get(route('archive.artists.index'))->assertOk()->assertSee('Thomas Cole');

        $slug = $artist->slugs()->where('active', true)->value('slug');

        $this->get(route('archive.artists.show', $slug))
            ->assertOk()
            ->assertSee('Thomas Cole')
            ->assertSee($artwork->title);
    }
}
