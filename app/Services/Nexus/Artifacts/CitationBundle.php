<?php

namespace App\Services\Nexus\Artifacts;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use InvalidArgumentException;

/**
 * The evidence behind one interpretive claim. Citations over vibes
 * (CLAUDE.md principle 5): an empty bundle cannot be constructed, so no
 * artifact carrying interpretive text can exist uncited.
 *
 * @implements IteratorAggregate<int, Citation>
 */
final readonly class CitationBundle implements Countable, IteratorAggregate
{
    /**
     * @param  list<Citation>  $citations
     */
    public function __construct(public array $citations)
    {
        if ($this->citations === []) {
            throw new InvalidArgumentException('A CitationBundle requires at least one citation.');
        }

        foreach ($this->citations as $citation) {
            if (!$citation instanceof Citation) {
                throw new InvalidArgumentException('A CitationBundle may only contain Citation instances.');
            }
        }
    }

    public function count(): int
    {
        return count($this->citations);
    }

    /**
     * @return ArrayIterator<int, Citation>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->citations);
    }
}
