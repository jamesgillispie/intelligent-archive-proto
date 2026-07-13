<?php

namespace App\Services\Memory;

use App\Models\User;
use App\Models\Memory\ArtworkSave;
use App\Models\Memory\MemoryEvent;
use Illuminate\Support\Facades\DB;
use App\Twill\Capsules\Artworks\Models\Artwork;

/**
 * Saves outside any trail — the lightest explicit signal.
 */
class ArtworkSaveService
{
    public function __construct(private readonly MemoryEventService $events) {}

    public function save(User $user, Artwork $artwork): ArtworkSave
    {
        $existing = $user->artworkSaves()->where('artwork_id', $artwork->id)->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $artwork) {
            $save = $user->artworkSaves()->create(['artwork_id' => $artwork->id]);

            $this->events->record($user, MemoryEvent::ARTWORK_SAVED, $save, [
                'artwork_title' => $artwork->title,
            ]);

            return $save;
        });
    }

    public function remove(ArtworkSave $save): void
    {
        DB::transaction(function () use ($save) {
            $this->events->record($save->user, MemoryEvent::SAVE_REMOVED, null, [
                'artwork_title' => $save->artwork->title,
            ]);

            $save->delete();
        });
    }
}
