<?php

namespace App\Models;

use App\Models\Memory\UserNote;
use App\Models\Memory\ArtworkSave;
use App\Models\Memory\MemoryEvent;
use Database\Factories\UserFactory;
use App\Models\Memory\MemoryConsent;
use App\Models\Memory\ResearchTrail;
use Illuminate\Notifications\Notifiable;
use App\Models\Memory\PersonalizationSetting;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    public function memoryConsents(): HasMany
    {
        return $this->hasMany(MemoryConsent::class);
    }

    /**
     * The latest consent grant; whether it is current for the configured
     * consent version is ConsentService's question.
     */
    public function memoryConsent(): HasOne
    {
        return $this->hasOne(MemoryConsent::class)->latestOfMany('granted_at');
    }

    public function personalizationSetting(): HasOne
    {
        return $this->hasOne(PersonalizationSetting::class);
    }

    public function trails(): HasMany
    {
        return $this->hasMany(ResearchTrail::class);
    }

    public function artworkSaves(): HasMany
    {
        return $this->hasMany(ArtworkSave::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(UserNote::class);
    }

    public function memoryEvents(): HasMany
    {
        return $this->hasMany(MemoryEvent::class);
    }
}
