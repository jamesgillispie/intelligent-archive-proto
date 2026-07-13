<?php

namespace App\Services\Nexus\Contracts;

use App\Services\Nexus\Artifacts\TrailSummary;
use App\Services\Nexus\Artifacts\ExploreResult;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Artifacts\TrailExpansion;
use App\Services\Nexus\Artifacts\ArtworkComparison;
use App\Services\Nexus\Artifacts\RelatedArtworkSet;

/**
 * Read side of the archive knowledge layer: intent-shaped methods
 * returning typed, cited artifacts — never raw hits. No visitor context
 * enters this boundary: trail expansion receives a neutral summary of
 * works, never a user or trail identity. MotifTrace stays deferred until
 * a surface consumes it.
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

    /**
     * Candidate additions for a set of works: continuations that deepen
     * the thread and deliberate counterpoints that broaden it, each
     * explained and cited. The summary is neutral — works and exclusions
     * only, never who is asking.
     */
    public function trailExpansion(TrailSummary $summary, ?int $limit = null): TrailExpansion;

    /**
     * Grounded similarities and differences between two or more works,
     * one citation-backed claim at a time.
     *
     * @param  list<Artwork>  $artworks two to four works
     */
    public function compare(array $artworks): ArtworkComparison;
}
