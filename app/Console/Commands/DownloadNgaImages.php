<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Import\DownloadArtworkImage;
use App\Services\Import\ArtworkImageDownloader;
use App\Twill\Capsules\Artworks\Models\ImageAsset;

/**
 * Backfill/resume entry point for the image pipeline. Selects primary
 * images that still need a download (or failed ones with --retry-failed)
 * and dispatches rate-limited jobs — so a partial run followed by a re-run
 * completes the remainder without re-downloading anything.
 */
class DownloadNgaImages extends Command
{
    protected $signature = 'nga:images
        {--limit= : Maximum downloads to dispatch this run}
        {--retry-failed : Also retry images whose downloads failed}
        {--force : Re-download even when a media file already exists}
        {--sync : Download inline instead of dispatching queue jobs}';

    protected $description = 'Download primary NGA images as Twill media for imported artworks';

    public function handle(ArtworkImageDownloader $downloader): int
    {
        $query = ImageAsset::query()
            ->where('is_primary', true)
            ->whereNotNull('iiif_url')
            ->whereHas('artwork')
            ->orderBy('id');

        if (!$this->option('force')) {
            $query->whereNull('media_id');

            if (!$this->option('retry-failed')) {
                $query->where('validation_status', '!=', ImageAsset::VALIDATION_FAILED);
            }
        }

        if ($this->option('limit') !== null) {
            $query->limit((int) $this->option('limit'));
        }

        $ids = $query->pluck('id');

        foreach ($ids as $id) {
            if ($this->option('sync')) {
                $image = ImageAsset::query()->find($id);

                if ($image !== null) {
                    // The downloader flags failures itself; a throw here is a
                    // transient error, which in sync mode we surface per image
                    // and continue — one bad download never stops the batch.
                    try {
                        $downloader->download($image, (bool) $this->option('force'));
                    } catch (\Throwable $exception) {
                        $this->warn("Image asset {$id}: {$exception->getMessage()}");
                    }
                }
            } else {
                DownloadArtworkImage::dispatch($id, (bool) $this->option('force'));
            }
        }

        $this->info(($this->option('sync') ? 'Downloaded' : 'Dispatched') . ": {$ids->count()} image(s).");

        $this->table(
            ['Primary image status', 'Count'],
            ImageAsset::query()
                ->where('is_primary', true)
                ->selectRaw("case when media_id is not null then 'downloaded' else validation_status end as status")
                ->selectRaw('count(*) as total')
                ->groupBy('status')
                ->orderBy('status')
                ->pluck('total', 'status')
                ->map(fn(int $count, string $status) => [$status, $count])
                ->values()
                ->all(),
        );

        return self::SUCCESS;
    }
}
