<?php

namespace App\Services\Nexus\Artifacts;

use Countable;
use ArrayIterator;
use IteratorAggregate;

/**
 * The answer to one non-personalized explore query: ranked matches, each
 * explaining which canonical fields it was found in.
 *
 * @implements IteratorAggregate<int, RelatedArtwork>
 */
final readonly class ExploreResult implements Countable, IteratorAggregate
{
    /**
     * @param  list<RelatedArtwork>  $matches ranked best-first
     */
    public function __construct(public string $query, public array $matches) {}

    public function isEmpty(): bool
    {
        return $this->matches === [];
    }

    /**
     * @return list<int>
     */
    public function artworkIds(): array
    {
        return array_map(fn(RelatedArtwork $match) => $match->artworkId, $this->matches);
    }

    public function count(): int
    {
        return count($this->matches);
    }

    /**
     * @return ArrayIterator<int, RelatedArtwork>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->matches);
    }
}
