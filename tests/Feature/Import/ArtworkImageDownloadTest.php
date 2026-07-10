<?php

namespace Tests\Feature\Import;

use Tests\TestCase;
use A17\Twill\Models\Media;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Import\DownloadArtworkImage;
use Illuminate\Http\Client\RequestException;
use App\Services\Import\ArtworkImageDownloader;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Twill\Capsules\Artworks\Models\ImageAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArtworkImageDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('twill.media_library.disk'));
    }

    /**
     * A real decodable JPEG, generated in-memory — the downloader inspects
     * the bytes, so a fake body must be an actual image.
     */
    protected function jpegBytes(int $width = 40, int $height = 50): string
    {
        $canvas = imagecreatetruecolor($width, $height);

        ob_start();
        imagejpeg($canvas);

        return (string) ob_get_clean();
    }

    protected function makePrimaryImage(array $attributes = []): ImageAsset
    {
        $artwork = Artwork::factory()
            ->withPrimaryImage(array_merge(['nga_uuid' => 'img-uuid-1'], $attributes))
            ->create(['title' => 'Test Painting']);

        return $artwork->images()->firstOrFail();
    }

    public function testDownloadAttachesTwillMediaWithAFullFrameCrop(): void
    {
        Http::fake(['api.nga.gov/*' => Http::response($this->jpegBytes(), 200)]);

        $image = $this->makePrimaryImage(['assistive_text' => 'A quiet landscape']);

        $result = app(ArtworkImageDownloader::class)->download($image);

        $this->assertSame(ArtworkImageDownloader::RESULT_DOWNLOADED, $result);

        // Sized IIIF request derived from the stored base URL.
        Http::assertSent(
            fn($request) => str_starts_with($request->url(), $image->iiif_url) &&
                str_contains($request->url(), '/full/!1600,1600/0/default.jpg'),
        );

        // File stored at a stable, re-derivable path on the media disk.
        Storage::disk(config('twill.media_library.disk'))->assertExists('img-uuid-1/img-uuid-1.jpg');

        // Twill media record carries the downloaded derivative's dimensions.
        $media = Media::where('uuid', 'img-uuid-1/img-uuid-1.jpg')->firstOrFail();
        $this->assertSame(40, $media->width);
        $this->assertSame(50, $media->height);
        $this->assertSame('A quiet landscape', $media->alt_text);

        // Attached under the artwork role with a full-frame default crop.
        $this->assertDatabaseHas('twill_mediables', [
            'mediable_type' => Artwork::class,
            'mediable_id' => $image->artwork_id,
            'media_id' => $media->id,
            'role' => Artwork::IMAGE_ROLE,
            'crop' => 'default',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => 40,
            'crop_h' => 50,
        ]);

        $image->refresh();
        $this->assertSame($media->id, $image->media_id);
        $this->assertSame(ImageAsset::VALIDATION_VALID, $image->validation_status);
        $this->assertNotNull($image->downloaded_at);

        $artwork = $image->artwork->fresh();
        $this->assertTrue($artwork->hasImage(Artwork::IMAGE_ROLE));
    }

    public function testMaxPixelsCapsTheRequestedSize(): void
    {
        Http::fake(['api.nga.gov/*' => Http::response($this->jpegBytes(), 200)]);

        $image = $this->makePrimaryImage(['max_pixels' => 640]);

        app(ArtworkImageDownloader::class)->download($image);

        Http::assertSent(fn($request) => str_contains($request->url(), '/full/!640,640/0/default.jpg'));
    }

    public function testClientErrorFlagsTheAssetWithoutCrashing(): void
    {
        Http::fake(['api.nga.gov/*' => Http::response('Not found', 404)]);

        $image = $this->makePrimaryImage();

        $result = app(ArtworkImageDownloader::class)->download($image);

        $this->assertSame(ArtworkImageDownloader::RESULT_FAILED, $result);

        $image->refresh();
        $this->assertSame(ImageAsset::VALIDATION_FAILED, $image->validation_status);
        $this->assertSame('http_404', $image->failure_reason);
        $this->assertNull($image->media_id);
        $this->assertSame(0, Media::count());
    }

    public function testUndecodableBodyFlagsTheAsset(): void
    {
        Http::fake(['api.nga.gov/*' => Http::response('this is not a jpeg', 200)]);

        $image = $this->makePrimaryImage();

        $result = app(ArtworkImageDownloader::class)->download($image);

        $this->assertSame(ArtworkImageDownloader::RESULT_FAILED, $result);
        $this->assertSame('undecodable_image', $image->fresh()->failure_reason);
    }

    public function testServerErrorThrowsSoTheQueueRetries(): void
    {
        Http::fake(['api.nga.gov/*' => Http::response('Upstream broke', 502)]);

        $image = $this->makePrimaryImage();

        try {
            app(ArtworkImageDownloader::class)->download($image);
            $this->fail('Expected a RequestException for the transient failure.');
        } catch (RequestException) {
            // Expected: transient errors must reach the queue's retry logic.
        }

        // Not flagged failed — the retry budget decides that, not one 502.
        $this->assertSame(ImageAsset::VALIDATION_UNVALIDATED, $image->fresh()->validation_status);
    }

    public function testResumeSkipsDownloadedAssetsUnlessForced(): void
    {
        Http::fake(['api.nga.gov/*' => Http::response($this->jpegBytes(), 200)]);

        $image = $this->makePrimaryImage();
        $downloader = app(ArtworkImageDownloader::class);

        $this->assertSame(ArtworkImageDownloader::RESULT_DOWNLOADED, $downloader->download($image));
        $this->assertSame(ArtworkImageDownloader::RESULT_SKIPPED, $downloader->download($image->fresh()));

        Http::assertSentCount(1);

        // Forcing re-downloads in place: still one media record, one pivot row.
        $this->assertSame(ArtworkImageDownloader::RESULT_DOWNLOADED, $downloader->download($image->fresh(), true));

        Http::assertSentCount(2);
        $this->assertSame(1, Media::count());
        $this->assertSame(1, $image->artwork->fresh()->medias()->wherePivot('role', Artwork::IMAGE_ROLE)->count());
    }

    public function testExhaustedRetriesFlagTheAsset(): void
    {
        $image = $this->makePrimaryImage();

        (new DownloadArtworkImage($image->id))->failed(new \RuntimeException('boom'));

        $image->refresh();
        $this->assertSame(ImageAsset::VALIDATION_FAILED, $image->validation_status);
        $this->assertSame('retries_exhausted', $image->failure_reason);
    }

    public function testImagesCommandDispatchesOnlyWhatStillNeedsDownloading(): void
    {
        Queue::fake();

        $pending = $this->makePrimaryImage();
        $downloaded = Artwork::factory()
            ->withPrimaryImage(['nga_uuid' => 'img-uuid-2'])
            ->create()
            ->images()
            ->firstOrFail();
        $failed = Artwork::factory()
            ->withPrimaryImage(['nga_uuid' => 'img-uuid-3'])
            ->create()
            ->images()
            ->firstOrFail();

        $media = Media::create([
            'uuid' => 'img-uuid-2/img-uuid-2.jpg',
            'filename' => 'img-uuid-2.jpg',
            'width' => 10,
            'height' => 10,
        ]);
        $downloaded->forceFill(['media_id' => $media->id])->save();
        $failed
            ->forceFill(['validation_status' => ImageAsset::VALIDATION_FAILED, 'failure_reason' => 'http_404'])
            ->save();

        $this->artisan('nga:images')->assertSuccessful();

        Queue::assertPushed(DownloadArtworkImage::class, 1);
        Queue::assertPushed(DownloadArtworkImage::class, fn($job) => $job->imageAssetId === $pending->id);

        // --retry-failed widens the selection to flagged assets.
        $this->artisan('nga:images', ['--retry-failed' => true])->assertSuccessful();

        Queue::assertPushed(DownloadArtworkImage::class, 3);
        Queue::assertPushed(DownloadArtworkImage::class, fn($job) => $job->imageAssetId === $failed->id);
    }

    public function testImportQueuesDownloadsForPrimaryImagesOnly(): void
    {
        // Fake only the download job: canonicalization must still run inline
        // (a blanket fake would capture the --sync jobs too).
        Queue::fake([DownloadArtworkImage::class]);

        $this->artisan('nga:import', [
            '--path' => base_path('tests/Fixtures/nga'),
            '--limit' => 10,
            '--sync' => true,
        ])->assertSuccessful();

        // Objects 100, 101, 104 are imported; 100 also has an alternate view
        // that must not be downloaded.
        Queue::assertPushed(DownloadArtworkImage::class, 3);

        $primaryIds = ImageAsset::where('is_primary', true)->pluck('id');
        Queue::assertPushed(DownloadArtworkImage::class, fn($job) => $primaryIds->contains($job->imageAssetId));

        // Re-import without downloads: nothing new is queued.
        Queue::fake([DownloadArtworkImage::class]);

        $this->artisan('nga:import', [
            '--path' => base_path('tests/Fixtures/nga'),
            '--limit' => 10,
            '--sync' => true,
            '--skip-images' => true,
        ])->assertSuccessful();

        Queue::assertNothingPushed();
    }
}
