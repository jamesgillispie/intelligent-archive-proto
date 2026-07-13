<?php

namespace App\Listeners;

use App\Events\ArtworkPromoted;
use App\Jobs\Indexing\IndexArtwork;

/**
 * Promotion → archive knowledge layer, always through the queue: the
 * import pipeline never blocks on (or knows about) indexing.
 */
class QueueArtworkIndexing
{
    public function handle(ArtworkPromoted $event): void
    {
        IndexArtwork::dispatch($event->artworkId);
    }
}
