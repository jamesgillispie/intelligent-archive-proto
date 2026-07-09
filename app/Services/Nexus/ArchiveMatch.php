<?php

namespace App\Services\Nexus;

/**
 * One related-artwork result. Citations over vibes (CLAUDE.md principle 5):
 * a match without citations cannot be constructed.
 */
final readonly class ArchiveMatch
{
    /**
     * @param  string  $reason human-readable explanation, shown to visitors verbatim
     * @param  array<int, array{field: string, artwork_id: int}>  $citations canonical fields grounding the match
     */
    public function __construct(
        public int $artworkId,
        public string $reason,
        public float $score,
        public array $citations,
    ) {
        if ($this->citations === []) {
            throw new \InvalidArgumentException('An ArchiveMatch requires at least one citation.');
        }
    }
}
