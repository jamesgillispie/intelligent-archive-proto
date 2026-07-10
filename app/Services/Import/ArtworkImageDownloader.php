<?php

namespace App\Services\Import;

use A17\Twill\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Twill\Capsules\Artworks\Models\ImageAsset;

/**
 * Downloads one NGA IIIF image and attaches it to its artwork as Twill
 * media (role `artwork`, full-frame free crop).
 *
 * Failure contract: permanent problems (4xx, undecodable image, missing
 * URL) flag the asset and return — they never crash a batch. Transient
 * problems (timeouts, 5xx) throw so the queue can retry. Idempotent and
 * resumable: an already-downloaded asset is skipped unless forced, and a
 * re-download overwrites the same media path rather than duplicating it.
 */
class ArtworkImageDownloader
{
    public const RESULT_DOWNLOADED = 'downloaded';
    public const RESULT_SKIPPED = 'skipped';
    public const RESULT_FAILED = 'failed';

    public function download(ImageAsset $image, bool $force = false): string
    {
        if (!$force && $image->isDownloaded()) {
            return self::RESULT_SKIPPED;
        }

        $url = $image->sizedUrl((int) config('nga.images.download_size'));

        if ($url === null) {
            return $this->markFailed($image, 'missing_iiif_url');
        }

        $response = Http::timeout((int) config('nga.iiif.timeout'))->accept('image/jpeg')->get($url);

        if ($response->clientError()) {
            return $this->markFailed($image, "http_{$response->status()}");
        }

        // Server errors and transport failures are transient: throw for retry.
        $response->throw();

        $bytes = $response->body();
        $dimensions = @getimagesizefromstring($bytes);

        if ($bytes === '' || $dimensions === false) {
            return $this->markFailed($image, 'undecodable_image');
        }

        /** @var Artwork|null $artwork */
        $artwork = $image->artwork;

        $media = $this->storeAsMedia($image, $artwork, $bytes, $dimensions[0], $dimensions[1]);

        if ($artwork !== null) {
            $this->attachToArtwork($artwork, $media);
        }

        $image
            ->forceFill([
                'media_id' => $media->id,
                'validation_status' => ImageAsset::VALIDATION_VALID,
                'validated_at' => now(),
                'downloaded_at' => now(),
                'failure_reason' => null,
            ])
            ->save();

        return self::RESULT_DOWNLOADED;
    }

    /**
     * One media record per NGA image, stored at a stable path so re-downloads
     * overwrite in place. Width/height are the downloaded derivative's actual
     * pixels; the native dimensions stay on the ImageAsset/source layer.
     */
    protected function storeAsMedia(ImageAsset $image, ?Artwork $artwork, string $bytes, int $width, int $height): Media
    {
        $path = "{$image->nga_uuid}/{$image->nga_uuid}.jpg";

        Storage::disk(config('twill.media_library.disk'))->put($path, $bytes);

        /** @var Media $media */
        $media = Media::query()->firstOrNew(['uuid' => $path]);

        $media
            ->fill([
                'uuid' => $path,
                'filename' => "{$image->nga_uuid}.jpg",
                'width' => $width,
                'height' => $height,
                'alt_text' => $image->altText($artwork->title ?? ''),
                'caption' => $artwork->title ?? null,
            ])
            ->save();

        return $media;
    }

    /**
     * Attach as the artwork's `artwork` role with a full-frame default crop
     * (see Artwork::getMediasParams — archival images are never cropped).
     */
    protected function attachToArtwork(Artwork $artwork, Media $media): void
    {
        DB::table(config('twill.mediables_table', 'twill_mediables'))
            ->where('mediable_type', $artwork->getMorphClass())
            ->where('mediable_id', $artwork->id)
            ->where('role', Artwork::IMAGE_ROLE)
            ->delete();

        $artwork->medias()->attach($media->id, [
            'role' => Artwork::IMAGE_ROLE,
            'crop' => 'default',
            'ratio' => 'default',
            'crop_x' => 0,
            'crop_y' => 0,
            'crop_w' => $media->width,
            'crop_h' => $media->height,
            'metadatas' => '{}',
        ]);
    }

    protected function markFailed(ImageAsset $image, string $reason): string
    {
        $image
            ->forceFill([
                'validation_status' => ImageAsset::VALIDATION_FAILED,
                'validated_at' => now(),
                'failure_reason' => $reason,
            ])
            ->save();

        // Identifiers and reason codes only — never payload contents.
        Log::warning('NGA image download failed', [
            'nga_uuid' => $image->nga_uuid,
            'artwork_id' => $image->artwork_id,
            'reason' => $reason,
        ]);

        return self::RESULT_FAILED;
    }
}
