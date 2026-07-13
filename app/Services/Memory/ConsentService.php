<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Models\Memory\MemoryEvent;
use Illuminate\Support\Facades\DB;
use App\Models\Memory\MemoryConsent;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Login is the consent boundary for durable memory. This service owns the
 * SQL consent record through its whole lifecycle — grant at registration,
 * renew after a wording change, pause/resume, revoke — and the memory
 * switches every write path checks. SQL is the durable record; the only
 * thing this service ever does to Redis Iris is remove context, because
 * pause and revocation both withdraw the basis for holding it.
 */
class ConsentService
{
    public function __construct(
        private readonly MemoryEventService $events,
        private readonly MemoryDeletionService $deletion,
        private readonly RedisIrisMemoryContract $iris,
        private readonly VisitorContextService $visitors,
    ) {}

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
     * Revocation ends the agreement, and the agreement was the only basis
     * for holding memory — so revoking first deletes everything held (SQL
     * and Iris, with purge verification), then stamps the consent rows.
     * The ledger keeps two receipts: the deletion, then the revocation.
     */
    public function revoke(User $user): void
    {
        $this->deletion->deleteAll($user);

        DB::transaction(function () use ($user) {
            $user
                ->memoryConsents()
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            $this->events->record($user, MemoryEvent::CONSENT_REVOKED);
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
     * The write-time gate for every durable-memory and Iris write: current
     * consent, and memory not paused. The user saves first, the system
     * infers second.
     */
    public function canRemember(User $user): bool
    {
        return $this->hasCurrentConsent($user) && !$this->isPaused($user);
    }

    /**
     * Pause stops writes, never reads — nothing durable is deleted, and
     * the dashboard keeps showing everything held. Derived Iris context is
     * purged (STRATEGY §7): while paused there is no basis for holding a
     * working profile, and it rebuilds from SQL on resume.
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

        $this->iris->purge($this->visitors->userPrefix($user));

        // Trail summaries are the owner's derived context too, just keyed
        // by trail; while paused there is no basis for holding them either.
        foreach ($user->trails()->pluck('id') as $trailId) {
            $this->iris->purge($this->visitors->trailPrefix((int) $trailId));
        }
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
