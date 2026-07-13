<?php

namespace App\Models\Memory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\Memory\UserNoteFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A freeform private note on a trail (or, later, an artwork). Never an AI
 * input without an explicit consent flag (STRATEGY §3).
 *
 * @mixin IdeHelperUserNote
 */
class UserNote extends Model
{
    /** @use HasFactory<UserNoteFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }
}
