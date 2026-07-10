<?php

namespace App\Jobs\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Import\ArtworkCanonicalizer;

/**
 * Promotes one NGA object into the canonical layer. Safe to retry:
 * canonicalization is idempotent per object.
 */
class CanonicalizeArtwork implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public string $ngaObjectId, public bool $downloadImages = true) {}

    public function handle(ArtworkCanonicalizer $canonicalizer): void
    {
        $canonicalizer->canonicalize($this->ngaObjectId, $this->downloadImages);
    }
}
