<?php

namespace App\Services\Memory;

use App\Models\User;

/**
 * The single mapping from a visitor to their Redis Iris namespace prefix.
 * Consented users get `user:{id}:`; anonymous visitors get `session:{id}:`
 * — strictly session-scoped, never linked to a user account without
 * explicit consent at login (that is the line between session state and
 * profiling). Whether anything may be *written* right now is consent
 * logic and lives on ConsentService::canRemember().
 */
class VisitorContextService
{
    public function userPrefix(User $user): string
    {
        return "user:{$user->id}:";
    }

    public function userNamespace(User $user, string $name): string
    {
        return $this->userPrefix($user) . $name;
    }

    /**
     * Anonymous context is keyed by session only, TTL-capped, and must
     * never accumulate into a profile. No consumer until the Phase 6
     * explore surface; the prefix contract exists so isolation from user
     * prefixes is testable now.
     */
    public function sessionPrefix(string $sessionId): string
    {
        return "session:{$sessionId}:";
    }

    public function sessionNamespace(string $sessionId, string $name): string
    {
        return $this->sessionPrefix($sessionId) . $name;
    }
}
