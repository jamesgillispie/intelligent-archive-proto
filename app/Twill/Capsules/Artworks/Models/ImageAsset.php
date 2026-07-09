<?php

namespace App\Twill\Capsules\Artworks\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A published NGA IIIF image for an artwork. Remote by design: NGA serves
 * open-access imagery over IIIF, so the Twill media library (uploaded
 * files) is deliberately not used for artwork imagery. The rights signal
 * lives here — an artwork without a validated image renders metadata-only.
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
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'max_pixels' => 'integer',
        'position' => 'integer',
        'validated_at' => 'datetime',
    ];

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
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
