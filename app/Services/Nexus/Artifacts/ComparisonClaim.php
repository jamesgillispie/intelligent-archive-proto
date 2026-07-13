<?php

namespace App\Services\Nexus\Artifacts;

/**
 * One grounded statement about the compared works. Whether it is a
 * similarity or a difference is carried by which ArtworkComparison list
 * it sits in; the statement is shown verbatim and the bundle quotes the
 * canonical fields behind it — a claim without evidence cannot exist.
 */
final readonly class ComparisonClaim
{
    public function __construct(public string $statement, public CitationBundle $citations) {}
}
