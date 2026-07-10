<?php

namespace App\Jobs\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\RateLimited;
use App\Services\Import\ArtworkImageDownloader;
use App\Twill\Capsules\Artworks\Models\ImageAsset;

/**
 * Downloads one artwork image through the polite `nga-iiif` rate limiter.
 *
 * Rate-limit releases count as attempts, so retry budget is expressed as
 * maxExceptions within a time window instead of a try count. A permanent
 * failure is flagged by the downloader without throwing; only transient
 * errors reach failed(), which flags the asset so the batch always
 * completes with an accounted-for status per image.
 */
class DownloadArtworkImage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $maxExceptions = 3;

    public function __construct(public int $imageAssetId, public bool $force = false) {}

    public function retryUntil(): \DateTime
    {
        return now()->addHours(6);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('nga-iiif')];
    }

    public function handle(ArtworkImageDownloader $downloader): void
    {
        $image = ImageAsset::query()->find($this->imageAssetId);

        if ($image === null) {
            return;
        }

        $downloader->download($image, $this->force);
    }

    public function failed(?\Throwable $exception): void
    {
        ImageAsset::query()
            ->whereKey($this->imageAssetId)
            ->update([
                'validation_status' => ImageAsset::VALIDATION_FAILED,
                'validated_at' => now(),
                'failure_reason' => 'retries_exhausted',
            ]);

        Log::warning('NGA image download retries exhausted', [
            'image_asset_id' => $this->imageAssetId,
            'exception' => $exception?->getMessage(),
        ]);
    }
}
