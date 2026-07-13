<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Models\Memory\MemoryConsent;

/**
 * Login is the consent boundary for durable memory. This service owns the
 * SQL consent record: granting it at registration and answering the two
 * questions the consented middleware asks — is consent current, and is
 * memory paused. Redis Iris never enters this layer; SQL is the durable
 * record.
 */
class ConsentService
{
    /**
     * Record consent for the current wording version and seed the user's
     * memory switches. Registration is the consent grant.
     */
    public function grant(User $user): MemoryConsent
    {
        $consent = $user->memoryConsents()->create([
            'consent_version' => config('memory.consent_version'),
            'granted_at' => now(),
        ]);

        $user->personalizationSetting()->firstOrCreate([]);

        return $consent;
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
}
