<?php

namespace App\Services\Nexus\Artifacts;

use Countable;
use ArrayIterator;
use IteratorAggregate;

/**
 * Ranked related works for one subject artwork, each explained and cited.
 *
 * @implements IteratorAggregate<int, RelatedArtwork>
 */
final readonly class RelatedArtworkSet implements Countable, IteratorAggregate
{
    /**
     * @param  list<RelatedArtwork>  $related ranked best-first
     */
    public function __construct(public int $subjectArtworkId, public array $related) {}

    public function isEmpty(): bool
    {
        return $this->related === [];
    }

    /**
     * @return list<int>
     */
    public function artworkIds(): array
    {
        return array_map(fn(RelatedArtwork $match) => $match->artworkId, $this->related);
    }

    public function count(): int
    {
        return count($this->related);
    }

    /**
     * @return ArrayIterator<int, RelatedArtwork>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->related);
    }
}
