<?php

namespace App\Twill\Capsules\Artworks\Models;

use A17\Twill\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A published NGA IIIF image for an artwork. The IIIF metadata (base URL,
 * native dimensions, rights cap) is the durable record — any size can be
 * re-derived from it without re-scanning the dataset. A downloaded copy is
 * attached to the artwork as Twill media (`media_id`); the download is a
 * cache of the IIIF source, never a replacement for it.
 *
 * @mixin IdeHelperImageAsset
 */
class ImageAsset extends Model
{
    public const VALIDATION_UNVALIDATED = 'unvalidated';
    public const VALIDATION_VALID = 'valid';
    public const VALIDATION_FAILED = 'failed';

    protected $table = 'artwork_images';

    protected $fillable = [
        'artwork_id',
        'nga_uuid',
        'iiif_url',
        'iiif_thumb_url',
        'view_type',
        'is_primary',
        'width',
        'height',
        'max_pixels',
        'assistive_text',
        'validation_status',
        'validated_at',
        'position',
        'media_id',
        'downloaded_at',
        'failure_reason',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'max_pixels' => 'integer',
        'position' => 'integer',
        'validated_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function isDownloaded(): bool
    {
        return $this->media_id !== null;
    }

    public function hasFailed(): bool
    {
        return $this->validation_status === self::VALIDATION_FAILED;
    }

    /**
     * Primary images that have never been downloaded and have not failed
     * permanently. Failed images are only retried explicitly.
     */
    public function needsDownload(): bool
    {
        return $this->is_primary && $this->iiif_url !== null && !$this->isDownloaded() && !$this->hasFailed();
    }

    /**
     * IIIF Image API URL constrained to fit a bounding box.
     */
    public function sizedUrl(int $size): ?string
    {
        if (!$this->iiif_url) {
            return null;
        }

        $size = $this->max_pixels ? min($size, $this->max_pixels) : $size;

        return "{$this->iiif_url}/full/!{$size},{$size}/0/default.jpg";
    }

    public function altText(string $fallback = ''): string
    {
        return $this->assistive_text ?: $fallback;
    }
}
