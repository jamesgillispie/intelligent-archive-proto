<?php

namespace App\Services\Nexus\Contracts;

use App\Services\Nexus\Artifacts\ExploreResult;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Artifacts\RelatedArtworkSet;

/**
 * Read side of the archive knowledge layer: intent-shaped methods
 * returning typed, cited artifacts — never raw hits. Both Phase 6 intents
 * are non-personalized; no visitor context enters this boundary.
 *
 * Phase 7 grows this contract with the personalized intents
 * (trailExpansion, compare, motifTrace) alongside their surfaces.
 */
interface NexusRetriever
{
    /**
     * Non-personalized related works for the artwork-detail panel. Every
     * result carries a human-readable reason and citations; safe for
     * publicly cached pages.
     */
    public function relatedTo(Artwork $artwork, ?int $limit = null): RelatedArtworkSet;

    /**
     * Non-personalized free-text exploration of the collection. Every
     * match explains which canonical fields it was found in.
     */
    public function explore(string $query, ?int $limit = null): ExploreResult;
}
