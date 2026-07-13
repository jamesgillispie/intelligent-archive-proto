<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Models\Memory\UserNote;
use App\Models\Memory\MemoryEvent;
use Illuminate\Support\Facades\DB;
use App\Models\Memory\ResearchTrail;
use Illuminate\Database\Eloquent\Model;
use App\Twill\Capsules\Artworks\Models\Artwork;

/**
 * Private freeform notes. Phase 4 UI writes trail notes; the morph target
 * is generic so artwork notes cost nothing later.
 */
class NoteService
{
    public function __construct(
        private readonly MemoryEventService $events,
        private readonly TrailMemoryService $trailMemory,
    ) {}

    public function write(User $user, Model $notable, string $body): UserNote
    {
        $note = DB::transaction(function () use ($user, $notable, $body) {
            $note = new UserNote(['body' => $body]);
            $note->user()->associate($user);
            $note->notable()->associate($notable);
            $note->save();

            $this->events->record($user, MemoryEvent::NOTE_WRITTEN, $note, $this->payloadFor($notable));

            if ($notable instanceof ResearchTrail) {
                $notable->touchActivity();
            }

            return $note;
        });

        if ($notable instanceof ResearchTrail) {
            $this->trailMemory->refreshActiveTrail($notable);
        }

        return $note;
    }

    public function delete(UserNote $note): void
    {
        DB::transaction(function () use ($note) {
            $payload = $note->notable !== null ? $this->payloadFor($note->notable) : [];

            $this->events->record($note->user, MemoryEvent::NOTE_DELETED, null, $payload);

            $note->delete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFor(Model $notable): array
    {
        return match (true) {
            $notable instanceof ResearchTrail => ['trail_title' => $notable->title],
            $notable instanceof Artwork => ['artwork_title' => $notable->title],
            default => [],
        };
    }
}
