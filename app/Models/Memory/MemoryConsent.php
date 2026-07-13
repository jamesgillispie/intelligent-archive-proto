<?php

namespace App\Models\Memory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\Memory\MemoryConsentFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * The consent record login creates. Authoritative gate every memory write
 * checks; export includes it; revocation triggers the deletion flow.
 *
 * @mixin IdeHelperMemoryConsent
 */
class MemoryConsent extends Model
{
    /** @use HasFactory<MemoryConsentFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'consent_version', 'granted_at', 'revoked_at'];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null;
    }
}
