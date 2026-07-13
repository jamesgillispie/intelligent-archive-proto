<?php

namespace App\Models\Memory;

use Illuminate\Database\Eloquent\Model;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Database\Factories\Memory\TrailItemFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * One artwork in a trail. The origin field keeps AI influence inspectable:
 * user_saved from day one, suggestion_accepted when recommendations arrive.
 *
 * @mixin IdeHelperTrailItem
 */
class TrailItem extends Model
{
    /** @use HasFactory<TrailItemFactory> */
    use HasFactory;

    public const ORIGIN_USER_SAVED = 'user_saved';

    public const ORIGIN_SUGGESTION_ACCEPTED = 'suggestion_accepted';

    protected $fillable = ['research_trail_id', 'artwork_id', 'position', 'origin', 'note'];

    protected $casts = ['position' => 'integer'];

    public function trail(): BelongsTo
    {
        return $this->belongsTo(ResearchTrail::class, 'research_trail_id');
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}
