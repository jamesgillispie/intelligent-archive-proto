<?php

namespace App\Services\Nexus;

use App\Models\Archive\ArtworkEnrichment;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Services\Nexus\Artifacts\ArtworkDocument;

/**
 * Assembles the indexable document for one artwork — the single place
 * this happens, and therefore the enforcement point for what may reach
 * the archive knowledge layer (STRATEGY §6):
 *
 * - canonical display values only (source-imported + editorial overrides),
 *   read from the Artwork model — never from raw source_records payloads;
 * - editorial description and APPROVED enrichments as identified
 *   passages; pending or rejected AI text is structurally excluded.
 */
class ArtworkDocumentBuilder
{
    public function build(Artwork $artwork): ArtworkDocument
    {
        $artwork->loadMissing(['artists', 'classification', 'department', 'medium']);

        return new ArtworkDocument(
            artworkId: $artwork->id,
            ngaObjectId: (string) $artwork->nga_object_id,
            fields: $this->fields($artwork),
            passages: $this->passages($artwork),
            edges: [
                'artist_ids' => $artwork->artists->pluck('id')->sort()->values()->all(),
                'classification_id' => $artwork->classification_id,
                'department_id' => $artwork->department_id,
                'medium_id' => $artwork->medium_id,
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    protected function fields(Artwork $artwork): array
    {
        $fields = [
            'title' => $artwork->title,
            'attribution' => $artwork->attribution,
            'display_date' => $artwork->display_date,
            'medium' => $artwork->medium?->name ?? $artwork->medium_text,
            'dimensions' => $artwork->dimensions,
            'credit_line' => $artwork->credit_line,
            'classification' => $artwork->classification?->name,
            'department' => $artwork->department?->name,
            'artists' => $artwork->artists->pluck('name')->join(', '),
        ];

        return array_filter(
            array_map(fn(?string $value) => trim((string) $value), $fields),
            fn(string $value) => $value !== '',
        );
    }

    /**
     * @return list<array{id: string, kind: string, text: string}>
     */
    protected function passages(Artwork $artwork): array
    {
        $passages = [];

        if (trim((string) $artwork->description) !== '') {
            $passages[] = [
                'id' => 'description',
                'kind' => 'editorial_description',
                'text' => trim(strip_tags((string) $artwork->description)),
            ];
        }

        if (trim((string) $artwork->source_inscription) !== '') {
            $passages[] = [
                'id' => 'inscription',
                'kind' => 'source_inscription',
                'text' => trim((string) $artwork->source_inscription),
            ];
        }

        // Approved only — pending and rejected AI text never leaves the
        // review table (STRATEGY §10: the indexer is the third layer of
        // defense against unreviewed AI metadata leaking).
        $approved = $artwork->enrichments()->approved()->orderBy('id')->get();

        foreach ($approved as $enrichment) {
            /** @var ArtworkEnrichment $enrichment */
            $passages[] = [
                'id' => "enrichment:{$enrichment->id}",
                'kind' => "approved_enrichment:{$enrichment->field}",
                'text' => $enrichment->generated_text,
            ];
        }

        return $passages;
    }
}
