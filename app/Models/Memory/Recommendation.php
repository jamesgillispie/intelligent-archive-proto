<?php

namespace App\Models\Memory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Database\Factories\Memory\RecommendationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * One suggestion the system actually showed, kept verbatim — reason,
 * evidence, and the context it was drawn from. "Why this suggestion?"
 * answers from this row, never from a fresh query, so the explanation is
 * what happened rather than a post-hoc rationalization. Dismissed rows
 * suppress the work from ever being suggested again.
 */
class Recommendation extends Model
{
    /** @use HasFactory<RecommendationFactory> */
    use HasFactory;

    public const STATUS_SHOWN = 'shown';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'user_id',
        'research_trail_id',
        'artwork_id',
        'kind',
        'reason',
        'citations',
        'context',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'citations' => 'array',
        'context' => 'array',
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trail(): BelongsTo
    {
        return $this->belongsTo(ResearchTrail::class, 'research_trail_id');
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_SHOWN;
    }

    /**
     * Shown and not yet answered — what the trail page keeps rendering.
     *
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SHOWN);
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopeDismissed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DISMISSED);
    }
}
