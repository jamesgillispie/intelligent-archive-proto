<?php

namespace App\Services\Trails;

use App\Models\User;
use App\Models\Memory\TrailItem;
use App\Models\Memory\MemoryEvent;
use Illuminate\Support\Facades\DB;
use App\Models\Memory\ResearchTrail;
use App\Services\Memory\ConsentService;
use App\Services\Memory\MemoryEventService;
use Illuminate\Validation\ValidationException;
use App\Twill\Capsules\Artworks\Models\Artwork;

/**
 * Every trail mutation is one transaction: the SQL rows and the ledger
 * event land together or not at all. Trails only ever contain what their
 * owner explicitly put there.
 */
class TrailService
{
    public function __construct(
        private readonly MemoryEventService $events,
        private readonly ConsentService $consent,
    ) {}

    public function start(
        User $user,
        string $title,
        ?string $intentNote = null,
        ?Artwork $firstArtwork = null,
        ?string $firstItemNote = null,
    ): ResearchTrail {
        if ($user->trails()->count() >= config('trails.max_per_user')) {
            throw ValidationException::withMessages([
                'title' => 'You have reached the trail limit. Delete a trail you no longer need to start another.',
            ]);
        }

        return DB::transaction(function () use ($user, $title, $intentNote, $firstArtwork, $firstItemNote) {
            $trail = $user->trails()->create([
                'title' => $title,
                'intent_note' => $intentNote,
                'last_activity_at' => now(),
            ]);

            $this->events->record($user, MemoryEvent::TRAIL_STARTED, $trail, ['trail_title' => $trail->title]);

            if ($firstArtwork !== null) {
                $this->addArtwork($trail, $firstArtwork, $firstItemNote);
            }

            return $trail;
        });
    }

    public function addArtwork(
        ResearchTrail $trail,
        Artwork $artwork,
        ?string $note = null,
        string $origin = TrailItem::ORIGIN_USER_SAVED,
    ): TrailItem {
        $existing = $trail->items()->where('artwork_id', $artwork->id)->first();

        if ($existing !== null) {
            // Saving the same work twice is idempotent, not an error.
            return $existing;
        }

        if ($trail->items()->count() >= config('trails.max_items')) {
            throw ValidationException::withMessages([
                'artwork_id' => "“{$trail->title}” has reached the item limit.",
            ]);
        }

        return DB::transaction(function () use ($trail, $artwork, $note, $origin) {
            $item = $trail->items()->create([
                'artwork_id' => $artwork->id,
                'position' => ((int) $trail->items()->max('position')) + 1,
                'origin' => $origin,
                'note' => $note,
            ]);

            $this->events->record($trail->user, MemoryEvent::ARTWORK_SAVED_TO_TRAIL, $item, [
                'artwork_title' => $artwork->title,
                'trail_title' => $trail->title,
            ]);

            $trail->touchActivity();

            return $item;
        });
    }

    public function removeItem(TrailItem $item): void
    {
        DB::transaction(function () use ($item) {
            $trail = $item->trail;

            $this->events->record($trail->user, MemoryEvent::TRAIL_ITEM_REMOVED, $trail, [
                'artwork_title' => $item->artwork->title,
                'trail_title' => $trail->title,
            ]);

            $item->delete();
            $trail->touchActivity();
        });
    }

    public function archive(ResearchTrail $trail): void
    {
        DB::transaction(function () use ($trail) {
            $trail->update(['status' => ResearchTrail::STATUS_ARCHIVED, 'last_activity_at' => now()]);

            $this->events->record($trail->user, MemoryEvent::TRAIL_ARCHIVED, $trail, [
                'trail_title' => $trail->title,
            ]);
        });
    }

    public function reactivate(ResearchTrail $trail): void
    {
        DB::transaction(function () use ($trail) {
            $trail->update(['status' => ResearchTrail::STATUS_ACTIVE, 'last_activity_at' => now()]);

            $this->events->record($trail->user, MemoryEvent::TRAIL_REACTIVATED, $trail, [
                'trail_title' => $trail->title,
            ]);
        });
    }

    public function delete(ResearchTrail $trail): void
    {
        DB::transaction(function () use ($trail) {
            // The ledger keeps a snapshot, not a reference — deletion is a
            // right, and the receipt must outlive the trail.
            $this->events->record($trail->user, MemoryEvent::TRAIL_DELETED, null, [
                'trail_title' => $trail->title,
                'item_count' => $trail->items()->count(),
            ]);

            $trail->notes()->delete();
            $trail->delete(); // items cascade at the database level
        });
    }

    /**
     * Trail revisits are one of the enumerated explicit signals — but only
     * deliberate returns count, so anything within the dedupe window of the
     * trail's last activity is silent, and pause stops the write entirely.
     */
    public function recordRevisit(ResearchTrail $trail): void
    {
        if ($this->consent->isPaused($trail->user)) {
            return;
        }

        if ($trail->last_activity_at->gt(now()->subMinutes(config('trails.revisit_dedupe_minutes')))) {
            return;
        }

        DB::transaction(function () use ($trail) {
            $this->events->record($trail->user, MemoryEvent::TRAIL_REVISITED, $trail, [
                'trail_title' => $trail->title,
            ]);

            $trail->touchActivity();
        });
    }
}
