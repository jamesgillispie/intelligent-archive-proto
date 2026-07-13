<?php

namespace App\Services\Import;

use App\Events\ArtworkPromoted;
use App\Models\Source\SourceRecord;
use App\Jobs\Import\DownloadArtworkImage;
use App\Twill\Capsules\Artists\Models\Artist;
use App\Twill\Capsules\Mediums\Models\Medium;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Twill\Capsules\Artworks\Models\ImageAsset;
use App\Twill\Capsules\Departments\Models\Department;
use App\Twill\Capsules\Classifications\Models\Classification;

/**
 * Promotes one NGA object from the source layer into the canonical layer.
 *
 * Reads exclusively from source_records (never from CSVs) and writes
 * imported values through HasSourceFields::applySourceValue, so editorial
 * overrides survive every re-import. Idempotent per object.
 */
class ArtworkCanonicalizer
{
    public function canonicalize(string $ngaObjectId, bool $downloadImages = true): ?Artwork
    {
        $objectRecord = SourceRecord::query()
            ->forTable(SourceRecord::TABLE_OBJECTS)
            ->where('source_id', $ngaObjectId)
            ->first();

        if ($objectRecord === null) {
            return null;
        }

        $payload = $objectRecord->payload;

        $artwork = Artwork::query()->firstOrNew(['nga_object_id' => $ngaObjectId]);

        if (!$artwork->exists) {
            // Published on create only: editors may unpublish later and a
            // re-import must never silently republish.
            $artwork->published = true;
        }

        $artwork->applySourceValue('title', $this->value($payload, 'title') ?? 'Untitled');
        $artwork->applySourceValue('display_date', $this->value($payload, 'displaydate'));
        $artwork->applySourceValue('medium_text', $this->value($payload, 'medium'));
        $artwork->applySourceValue('dimensions', $this->value($payload, 'dimensions'));
        $artwork->applySourceValue('credit_line', $this->value($payload, 'creditline'));
        $artwork->applySourceValue('attribution', $this->value($payload, 'attribution'));

        $artwork->fill([
            'accession_number' => $this->value($payload, 'accessionnum'),
            'source_record_id' => $objectRecord->id,
            'source_begin_year' => $this->intValue($payload, 'beginyear'),
            'source_end_year' => $this->intValue($payload, 'endyear'),
            'source_inscription' => $this->value($payload, 'inscription'),
            'source_classification' => $this->value($payload, 'classification'),
            'source_subclassification' => $this->value($payload, 'subclassification'),
            'source_department_abbr' => $this->value($payload, 'departmentabbr'),
            'classification_id' => $this->vocabularyId(Classification::class, $this->value($payload, 'classification')),
            'department_id' => $this->vocabularyId(Department::class, $this->value($payload, 'departmentabbr')),
            'medium_id' => $this->vocabularyId(Medium::class, $this->value($payload, 'medium')),
            'last_synced_at' => now(),
        ]);

        $artwork->save();

        $imageCount = $this->syncImages($artwork, $downloadImages);
        $this->syncArtists($artwork);

        // Displayable requires at least one primary image (rights signal).
        $artwork->forceFill(['is_displayable' => $imageCount > 0])->save();

        // Promotion is complete; downstream layers (Nexus indexing) react
        // to the event — ingestion itself never calls them (STRATEGY §3.8).
        ArtworkPromoted::dispatch($artwork->id);

        return $artwork;
    }

    protected function syncImages(Artwork $artwork, bool $downloadImages): int
    {
        $imageRecords = SourceRecord::query()
            ->forTable(SourceRecord::TABLE_PUBLISHED_IMAGES)
            ->where('payload->depictstmsobjectid', $artwork->nga_object_id)
            ->get();

        $primaryCount = 0;

        foreach ($imageRecords as $record) {
            $payload = $record->payload;
            $isPrimary = ($payload['viewtype'] ?? '') === 'primary';
            $primaryCount += $isPrimary ? 1 : 0;

            $asset = ImageAsset::updateOrCreate(
                ['nga_uuid' => $record->source_id],
                [
                    'artwork_id' => $artwork->id,
                    'iiif_url' => $this->value($payload, 'iiifurl'),
                    'iiif_thumb_url' => $this->value($payload, 'iiifthumburl'),
                    'view_type' => $this->value($payload, 'viewtype'),
                    'is_primary' => $isPrimary,
                    'width' => $this->intValue($payload, 'width'),
                    'height' => $this->intValue($payload, 'height'),
                    'max_pixels' => $this->intValue($payload, 'maxpixels'),
                    'assistive_text' => $this->value($payload, 'assistivetext'),
                    'position' => $this->intValue($payload, 'sequence') ?? 0,
                ],
            );

            if ($downloadImages && $asset->is_primary) {
                $this->dispatchDownload($asset);
            }
        }

        return $primaryCount;
    }

    /**
     * Resumable by construction: never-downloaded assets are queued, an
     * upstream IIIF URL change forces a refresh of the existing download,
     * and everything else (already downloaded, previously failed) is left
     * alone — failed images are only retried via `nga:images --retry-failed`.
     */
    protected function dispatchDownload(ImageAsset $asset): void
    {
        if ($asset->needsDownload()) {
            DownloadArtworkImage::dispatch($asset->id);
        } elseif ($asset->isDownloaded() && $asset->wasChanged('iiif_url')) {
            DownloadArtworkImage::dispatch($asset->id, force: true);
        }
    }

    protected function syncArtists(Artwork $artwork): void
    {
        // Ordered by source_id so that when a pair carries several role rows
        // the pivot role resolves deterministically (last key wins).
        $joinRecords = SourceRecord::query()
            ->forTable(SourceRecord::TABLE_OBJECTS_CONSTITUENTS)
            ->where('payload->objectid', $artwork->nga_object_id)
            ->orderBy('source_id')
            ->get()
            ->filter(fn(SourceRecord $record) => ($record->payload['roletype'] ?? '') === 'artist');

        $attach = [];

        foreach ($joinRecords as $join) {
            $constituentId = $join->payload['constituentid'] ?? null;

            if (!$constituentId) {
                continue;
            }

            $artist = $this->canonicalizeArtist((string) $constituentId);

            if ($artist === null) {
                continue;
            }

            $attach[$artist->id] = [
                'role' => $this->value($join->payload, 'role') ?? 'artist',
                'position' => $this->intValue($join->payload, 'displayorder') ?? 0,
            ];
        }

        if ($attach !== []) {
            $artwork->artists()->syncWithoutDetaching($attach);
        }
    }

    protected function canonicalizeArtist(string $constituentId): ?Artist
    {
        $record = SourceRecord::query()
            ->forTable(SourceRecord::TABLE_CONSTITUENTS)
            ->where('source_id', $constituentId)
            ->first();

        if ($record === null) {
            return null;
        }

        $payload = $record->payload;

        $artist = Artist::query()->firstOrNew(['nga_constituent_id' => $constituentId]);

        if (!$artist->exists) {
            $artist->published = true;
        }

        $artist->applySourceValue('name', $this->value($payload, 'preferreddisplayname') ?? 'Unknown artist');
        $artist->applySourceValue('display_date', $this->value($payload, 'displaydate'));

        $artist->fill([
            'source_record_id' => $record->id,
            'nationality' =>
                $this->value($payload, 'visualbrowsernationality') ?? $this->value($payload, 'nationality'),
            'begin_year' => $this->intValue($payload, 'beginyear'),
            'end_year' => $this->intValue($payload, 'endyear'),
            'ulan_id' => $this->value($payload, 'ulanid'),
            'wikidata_id' => $this->value($payload, 'wikidataid'),
            'artist_of_nga_object' => ($payload['artistofngaobject'] ?? '1') !== '0',
            'last_synced_at' => now(),
        ]);

        $artist->save();

        return $artist;
    }

    /**
     * @param  class-string<Classification|Department|Medium>  $model
     */
    protected function vocabularyId(string $model, ?string $sourceName): ?int
    {
        if ($sourceName === null || mb_strlen($sourceName) > 255) {
            return null;
        }

        /** @var Classification|Department|Medium $vocabulary */
        $vocabulary = $model
            ::query()
            ->firstOrCreate(['source_name' => $sourceName], ['name' => $sourceName, 'published' => true]);

        return $vocabulary->id;
    }

    /**
     * @param  array<string, string>  $payload
     */
    protected function value(array $payload, string $key): ?string
    {
        $value = trim((string) ($payload[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, string>  $payload
     */
    protected function intValue(array $payload, string $key): ?int
    {
        $value = $this->value($payload, $key);

        return is_numeric($value) ? (int) $value : null;
    }
}
