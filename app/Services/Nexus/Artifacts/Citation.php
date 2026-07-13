<?php

namespace App\Services\Nexus\Artifacts;

/**
 * One piece of evidence: a canonical field or identified passage on a
 * specific artwork, with the value quoted so the grounding is inspectable
 * by a human, not just resolvable by a machine.
 */
final readonly class Citation
{
    public function __construct(
        public int $artworkId,
        // A canonical field name ('medium') or passage id ('enrichment:12').
        public string $field,
        public string $value,
    ) {}

    /**
     * @return array{artwork_id: int, field: string, value: string}
     */
    public function toArray(): array
    {
        return [
            'artwork_id' => $this->artworkId,
            'field' => $this->field,
            'value' => $this->value,
        ];
    }
}
