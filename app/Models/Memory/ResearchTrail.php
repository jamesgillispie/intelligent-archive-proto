<?php

namespace App\Models\Memory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Database\Factories\Memory\ResearchTrailFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A named research trail — the core product object. Trails, not feeds:
 * a trail only ever contains what its owner explicitly put there.
 *
 * @mixin IdeHelperResearchTrail
 */
class ResearchTrail extends Model
{
    /** @use HasFactory<ResearchTrailFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = ['user_id', 'title', 'intent_note', 'status', 'last_activity_at'];

    protected $casts = ['last_activity_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TrailItem::class)->orderBy('position');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(UserNote::class, 'notable')->latest();
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function containsArtwork(Artwork $artwork): bool
    {
        return $this->items()->where('artwork_id', $artwork->id)->exists();
    }

    public function touchActivity(): void
    {
        $this->forceFill(['last_activity_at' => now()])->save();
    }

    /**
     * @param Builder<self> $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
