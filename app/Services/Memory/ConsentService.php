<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Models\Memory\MemoryEvent;
use Illuminate\Support\Facades\DB;
use App\Models\Memory\MemoryConsent;

/**
 * Login is the consent boundary for durable memory. This service owns the
 * SQL consent record — granting it at registration, renewing it after a
 * wording change — and the memory switches: is consent current, is memory
 * paused. Redis Iris never enters this layer; SQL is the durable record.
 */
class ConsentService
{
    public function __construct(private readonly MemoryEventService $events) {}

    /**
     * Record consent for the current wording version and seed the user's
     * memory switches. Registration is the consent grant. Re-granting a
     * version that was revoked reopens the same row.
     */
    public function grant(User $user): MemoryConsent
    {
        $consent = $user
            ->memoryConsents()
            ->updateOrCreate(
                ['consent_version' => config('memory.consent_version')],
                ['granted_at' => now(), 'revoked_at' => null],
            );

        $user->personalizationSetting()->firstOrCreate([]);

        return $consent;
    }

    /**
     * Re-consent after the wording changed (or after a revocation). The
     * renewal itself goes in the ledger — consent state changes are memory
     * the user can inspect.
     */
    public function renew(User $user): MemoryConsent
    {
        return DB::transaction(function () use ($user) {
            $consent = $this->grant($user);

            $this->events->record($user, MemoryEvent::CONSENT_RENEWED);

            return $consent;
        });
    }

    /**
     * Consent is current when the latest grant is unrevoked and matches the
     * consent wording version in config; users on older versions are
     * re-prompted rather than silently carried forward.
     */
    public function hasCurrentConsent(User $user): bool
    {
        $consent = $user->memoryConsent;

        return $consent !== null &&
            $consent->isActive() &&
            $consent->consent_version === config('memory.consent_version');
    }

    public function isPaused(User $user): bool
    {
        return (bool) $user->personalizationSetting?->memory_paused;
    }

    /**
     * Pause stops writes, never reads — nothing is deleted, and the
     * dashboard keeps showing everything held.
     */
    public function pause(User $user): void
    {
        $setting = $user->personalizationSetting()->firstOrCreate([]);

        if ($setting->memory_paused) {
            return;
        }

        DB::transaction(function () use ($user, $setting) {
            $setting->update(['memory_paused' => true]);

            $this->events->record($user, MemoryEvent::MEMORY_PAUSED);
        });
    }

    public function resume(User $user): void
    {
        $setting = $user->personalizationSetting()->firstOrCreate([]);

        if (!$setting->memory_paused) {
            return;
        }

        DB::transaction(function () use ($user, $setting) {
            $setting->update(['memory_paused' => false]);

            $this->events->record($user, MemoryEvent::MEMORY_RESUMED);
        });
    }
}
