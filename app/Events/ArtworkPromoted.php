<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired when the import pipeline finishes promoting one NGA object into
 * the canonical layer (STRATEGY §3.8). Ingestion never calls AI services
 * directly — downstream layers subscribe to this instead; the Nexus
 * indexing job is the first listener.
 */
class ArtworkPromoted
{
    use Dispatchable;

    public function __construct(public int $artworkId) {}
}
