<?php

namespace App\Models\Archive;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Archive\ArtworkEnrichmentFactory;

/**
 * AI-generated field values awaiting review (STRATEGY §4). Generated text
 * lives here — never in display columns — until a human approves it;
 * approval copies the value into the corresponding editorial override
 * with a review trail. The Nexus document builder indexes approved rows
 * only, so unreviewed AI text is structurally unable to reach either the
 * display layer or the archive knowledge layer.
 *
 * Nothing generates enrichments yet; the model exists so that exclusion
 * is enforced and testable from the first indexed document.
 *
 * @mixin IdeHelperArtworkEnrichment
 */
class ArtworkEnrichment extends Model
{
    /** @use HasFactory<ArtworkEnrichmentFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'artwork_id',
        'field',
        'generated_text',
        'model_ref',
        'prompt_ref',
        'status',
        'reviewed_by',
        'reviewed_at',
        'citations',
    ];

    protected $casts = [
        'citations' => 'array',
        'reviewed_at' => 'datetime',
    ];

    protected static function newFactory(): ArtworkEnrichmentFactory
    {
        return ArtworkEnrichmentFactory::new();
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
