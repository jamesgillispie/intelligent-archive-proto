<?php

namespace App\Services\Nexus\Artifacts;

/**
 * Grounded similarities and differences between two or more works —
 * citations beating vibes. Both lists may be empty when the record holds
 * too little to compare; nothing is invented to fill the space.
 */
final readonly class ArtworkComparison
{
    /**
     * @param  list<int>  $artworkIds the compared works, in request order
     * @param  list<ComparisonClaim>  $similarities
     * @param  list<ComparisonClaim>  $differences
     */
    public function __construct(public array $artworkIds, public array $similarities, public array $differences) {}

    public function isEmpty(): bool
    {
        return $this->similarities === [] && $this->differences === [];
    }
}
