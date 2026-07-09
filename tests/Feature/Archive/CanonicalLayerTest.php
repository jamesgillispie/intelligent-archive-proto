<?php

namespace Tests\Feature\Archive;

use Tests\TestCase;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Mediums\Models\Medium;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Twill\Capsules\Classifications\Models\Classification;

class CanonicalLayerTest extends TestCase
{
    use RefreshDatabase;

    public function testArtworkGetsASlugOnProgrammaticSave(): void
    {
        $artwork = Artwork::factory()->create(['title' => 'The Oxbow View', 'source_title' => 'The Oxbow View']);

        $slug = $artwork->slugs()->where('active', true)->value('slug');

        $this->assertNotNull($slug);
        $this->assertSame($artwork->id, Artwork::forSlug($slug)->first()?->id);
    }

    public function testArtworkRelationships(): void
    {
        $classification = Classification::create([
            'published' => true,
            'name' => 'Painting',
            'source_name' => 'Painting',
        ]);
        $medium = Medium::create(['published' => true, 'name' => 'oil on canvas', 'source_name' => 'oil on canvas']);

        $artwork = Artwork::factory()
            ->withPrimaryImage()
            ->create(['classification_id' => $classification->id, 'medium_id' => $medium->id]);

        $artist = Artist::factory()->create();
        $artwork->artists()->attach($artist->id, ['role' => 'artist', 'position' => 0]);

        $artwork->refresh();

        $this->assertSame('Painting', $artwork->classification->name);
        $this->assertSame('oil on canvas', $artwork->medium->name);
        $this->assertSame($artist->id, $artwork->artists->first()->id);
        $this->assertDatabaseHas('artwork_artist', [
            'artwork_id' => $artwork->id,
            'artist_id' => $artist->id,
            'role' => 'artist',
        ]);
        $this->assertTrue($artwork->primaryImage->is_primary);
        $this->assertTrue($artist->artworks()->whereKey($artwork->id)->exists());
    }

    public function testMediumUsesExplicitTableAvoidingTwillMediaCollision(): void
    {
        $medium = Medium::create(['published' => true, 'name' => 'tempera', 'source_name' => 'tempera']);

        $this->assertSame('mediums', $medium->getTable());
        $this->assertSame(1, Medium::count());
    }

    public function testSourceValueRefreshRespectsEditorialOverride(): void
    {
        $artwork = Artwork::factory()->create([
            'title' => 'Original Imported Title',
            'source_title' => 'Original Imported Title',
        ]);

        $this->assertFalse($artwork->isOverridden('title'));

        // Upstream changes, no editorial override: display follows source.
        $artwork->applySourceValue('title', 'Corrected Upstream Title');
        $artwork->save();

        $this->assertSame('Corrected Upstream Title', $artwork->title);
        $this->assertFalse($artwork->isOverridden('title'));

        // Editor overrides in Twill.
        $artwork->update(['title' => 'Editorial Title']);
        $this->assertTrue($artwork->isOverridden('title'));

        // Next refresh updates the source value but never the display value.
        $artwork->applySourceValue('title', 'Second Upstream Title');
        $artwork->save();

        $this->assertSame('Editorial Title', $artwork->fresh()->title);
        $this->assertSame('Second Upstream Title', $artwork->fresh()->source_title);
        $this->assertTrue($artwork->fresh()->isOverridden('title'));

        $provenance = $artwork->fresh()->fieldProvenance();
        $this->assertTrue($provenance['title']['overridden']);
        $this->assertSame('Second Upstream Title', $provenance['title']['source_value']);
    }

    public function testDisplayableScopeRequiresPublishedAndDisplayable(): void
    {
        Artwork::factory()->create(['published' => true, 'is_displayable' => true]);
        Artwork::factory()->create(['published' => true, 'is_displayable' => false]);
        Artwork::factory()->create(['published' => false, 'is_displayable' => true]);

        $this->assertSame(1, Artwork::displayable()->count());
    }
}
