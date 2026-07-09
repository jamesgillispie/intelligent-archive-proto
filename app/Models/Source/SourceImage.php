<?php

namespace App\Models\Source;

use Illuminate\Database\Eloquent\Builder;

/**
 * Typed view over source_records rows from published_images.csv.
 *
 * Single-table: shares the source_records table with a global scope; exists
 * for ergonomic access to IIIF/rights fields of the raw image rows.
 *
 * @mixin IdeHelperSourceImage
 */
class SourceImage extends SourceRecord
{
    protected $table = 'source_records';

    protected static function booted(): void
    {
        static::addGlobalScope('published_images', function (Builder $query) {
            $query->where('source_table', self::TABLE_PUBLISHED_IMAGES);
        });

        static::creating(function (SourceImage $image) {
            $image->source_table = self::TABLE_PUBLISHED_IMAGES;
        });
    }

    public function iiifUrl(): ?string
    {
        return $this->payloadValue('iiifurl');
    }

    public function iiifThumbUrl(): ?string
    {
        return $this->payloadValue('iiifthumburl');
    }

    public function viewType(): ?string
    {
        return $this->payloadValue('viewtype');
    }

    public function depictsObjectId(): ?string
    {
        return $this->payloadValue('depictstmsobjectid');
    }

    public function assistiveText(): ?string
    {
        return $this->payloadValue('assistivetext');
    }
}
