<?php

namespace App\Jobs\Indexing;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\Nexus\ArtworkDocumentBuilder;
use App\Services\Nexus\Contracts\NexusIndexer;
use App\Twill\Capsules\Artworks\Models\Artwork;

/**
 * Keeps one artwork's Nexus document in step with the canonical layer.
 * The only caller of NexusIndexer. Idempotent per artwork: displayable →
 * upsert a freshly built document; missing, unpublished, or otherwise
 * undisplayable → remove. Never touches raw source data.
 */
class IndexArtwork implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $artworkId) {}

    public function handle(ArtworkDocumentBuilder $builder, NexusIndexer $indexer): void
    {
        $artwork = Artwork::query()
            ->with(['artists', 'classification', 'department', 'medium'])
            ->find($this->artworkId);

        if ($artwork === null || !$artwork->published || !$artwork->is_displayable) {
            $indexer->removeArtwork($this->artworkId);

            return;
        }

        $indexer->upsertArtwork($builder->build($artwork));
    }
}
