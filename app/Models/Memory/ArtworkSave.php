<?php

namespace App\Models\Memory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Twill\Capsules\Artworks\Models\Artwork;
use Database\Factories\Memory\ArtworkSaveFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A save outside any trail — the lightest explicit signal (STRATEGY §3).
 *
 * @mixin IdeHelperArtworkSave
 */
class ArtworkSave extends Model
{
    /** @use HasFactory<ArtworkSaveFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'artwork_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }
}
