<?php

namespace App\Services\Nexus;

use Illuminate\Support\Collection;
use App\Twill\Capsules\Artworks\Models\Artwork;

/**
 * Archive knowledge layer boundary (Pinecone Nexus).
 *
 * Vendor API rule (CLAUDE.md): this contract is interface-level only. No
 * request/response shapes, SDK calls, or endpoint assumptions live behind
 * it until official vendor docs are provided in a session. The fake
 * implementation is the default and defines the behavioral bar: every
 * relatedness result must carry a human-readable reason and citations.
 */
interface NexusArchiveContract
{
    /**
     * Make an artwork's canonical (never raw, never unapproved-AI) data
     * available to the archive knowledge layer.
     */
    public function indexArtwork(Artwork $artwork): void;

    /**
     * Remove an artwork from the archive knowledge layer.
     */
    public function removeArtwork(int $artworkId): void;

    /**
     * Non-personalized related works with reasons and citations.
     *
     * @return Collection<int, ArchiveMatch>
     */
    public function relatedTo(Artwork $artwork, ?int $limit = null): Collection;
}
