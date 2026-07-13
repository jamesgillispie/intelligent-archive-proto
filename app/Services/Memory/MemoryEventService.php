<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Models\Memory\MemoryEvent;
use Illuminate\Database\Eloquent\Model;

/**
 * The one way ledger rows are written. Callers pass a payload snapshot
 * (titles, counts) so the row stays human-readable after its subject is
 * deleted — the ledger is for the user's eyes first.
 */
class MemoryEventService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function record(User $user, string $type, ?Model $subject = null, array $payload = []): MemoryEvent
    {
        $event = new MemoryEvent([
            'event_type' => $type,
            'payload' => $payload ?: null,
            'occurred_at' => now(),
        ]);

        $event->user()->associate($user);

        if ($subject !== null) {
            $event->subject()->associate($subject);
        }

        $event->save();

        return $event;
    }
}
