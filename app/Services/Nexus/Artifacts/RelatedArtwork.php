<?php

namespace App\Services\Nexus\Artifacts;

/**
 * One related or matching artwork. The reason is shown to visitors
 * verbatim (human-readable relationships, CLAUDE.md principle 4); the
 * bundle grounds it in canonical fields.
 */
final readonly class RelatedArtwork
{
    public const KIND_SHARED_ARTIST = 'shared_artist';

    public const KIND_SHARED_METADATA = 'shared_metadata';

    public const KIND_QUERY_MATCH = 'query_match';

    public function __construct(
        public int $artworkId,
        public string $kind,
        public string $reason,
        public float $score,
        public CitationBundle $citations,
    ) {}
}
