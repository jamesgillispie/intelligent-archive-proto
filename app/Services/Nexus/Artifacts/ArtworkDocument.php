<?php

namespace App\Services\Nexus\Artifacts;

/**
 * The indexable unit for one displayable artwork, assembled exclusively
 * from canonical data by ArtworkDocumentBuilder. Fields and passages carry
 * stable identifiers so any retrieved evidence can be cited back to a
 * specific field or text entry (STRATEGY §6).
 */
final readonly class ArtworkDocument
{
    /**
     * @param  array<string, string>  $fields structured metadata, keyed by canonical field name
     * @param  list<array{id: string, kind: string, text: string}>  $passages identified textual material
     * @param  array{artist_ids: list<int>, classification_id: ?int, department_id: ?int, medium_id: ?int}  $edges
     */
    public function __construct(
        public int $artworkId,
        public string $ngaObjectId,
        public array $fields,
        public array $passages,
        public array $edges,
    ) {}
}
