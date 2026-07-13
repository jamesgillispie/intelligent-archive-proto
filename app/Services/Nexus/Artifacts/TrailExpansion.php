<?php

namespace App\Services\Nexus\Artifacts;

/**
 * Candidate additions for a trail: continuations that deepen the thread
 * and deliberate counterpoints that broaden it (charter principle 10 —
 * broaden understanding, never maximize attention). Every entry is a
 * RelatedArtwork, so it is explained and cited by construction.
 */
final readonly class TrailExpansion
{
    /**
     * @param  list<RelatedArtwork>  $continuations ranked best-first
     * @param  list<RelatedArtwork>  $counterpoints ranked best-first
     */
    public function __construct(public array $continuations, public array $counterpoints) {}

    public function isEmpty(): bool
    {
        return $this->continuations === [] && $this->counterpoints === [];
    }

    /**
     * @return list<RelatedArtwork>
     */
    public function all(): array
    {
        return [...$this->continuations, ...$this->counterpoints];
    }

    /**
     * @return list<int>
     */
    public function artworkIds(): array
    {
        return array_map(fn(RelatedArtwork $suggestion) => $suggestion->artworkId, $this->all());
    }
}
