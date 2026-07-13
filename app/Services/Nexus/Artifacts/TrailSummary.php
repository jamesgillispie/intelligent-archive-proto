<?php

namespace App\Services\Nexus\Artifacts;

use InvalidArgumentException;

/**
 * Retrieval input for trail expansion — deliberately neutral: ordered
 * artwork ids plus works to leave out (already in the trail, already
 * suggested, or dismissed). No user id, no trail id, nothing identifying
 * crosses into the archive layer; Nexus knows the archive, never the
 * visitor.
 */
final readonly class TrailSummary
{
    /**
     * @param  list<int>  $artworkIds trail works, in trail order
     * @param  list<int>  $excludeArtworkIds never suggest these
     */
    public function __construct(public array $artworkIds, public array $excludeArtworkIds = [])
    {
        if ($this->artworkIds === []) {
            throw new InvalidArgumentException('A trail summary needs at least one artwork.');
        }
    }
}
