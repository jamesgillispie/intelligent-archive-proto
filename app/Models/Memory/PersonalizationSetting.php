<?php

namespace App\Models\Memory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Memory\PersonalizationSettingFactory;

/**
 * The user's memory switches. Pause != delete: pausing stops writes while
 * the durable SQL record stays intact and readable.
 *
 * @mixin IdeHelperPersonalizationSetting
 */
class PersonalizationSetting extends Model
{
    /** @use HasFactory<PersonalizationSettingFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'memory_paused', 'semantic_suggestions_enabled'];

    protected $casts = [
        'memory_paused' => 'boolean',
        'semantic_suggestions_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
