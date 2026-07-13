<?php

namespace App\Models\Memory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\Memory\MemoryEventFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Append-only ledger of every explicit action — the only personalization
 * input stream there is, fully visible on the memory dashboard. Payloads
 * snapshot titles so a row stays readable after its subject is deleted.
 *
 * @mixin IdeHelperMemoryEvent
 */
class MemoryEvent extends Model
{
    /** @use HasFactory<MemoryEventFactory> */
    use HasFactory;

    public const TRAIL_STARTED = 'trail_started';

    public const TRAIL_REVISITED = 'trail_revisited';

    public const TRAIL_ARCHIVED = 'trail_archived';

    public const TRAIL_REACTIVATED = 'trail_reactivated';

    public const TRAIL_DELETED = 'trail_deleted';

    public const ARTWORK_SAVED_TO_TRAIL = 'artwork_saved_to_trail';

    public const TRAIL_ITEM_REMOVED = 'trail_item_removed';

    public const ARTWORK_SAVED = 'artwork_saved';

    public const SAVE_REMOVED = 'save_removed';

    public const NOTE_WRITTEN = 'note_written';

    public const NOTE_DELETED = 'note_deleted';

    public const MEMORY_PAUSED = 'memory_paused';

    public const MEMORY_RESUMED = 'memory_resumed';

    public const CONSENT_RENEWED = 'consent_renewed';

    public const CONSENT_REVOKED = 'consent_revoked';

    public const MEMORY_DELETED = 'memory_deleted';

    public const SUGGESTION_DISMISSED = 'suggestion_dismissed';

    public const COMPARISON_REQUESTED = 'comparison_requested';

    public $timestamps = false;

    protected $fillable = ['user_id', 'event_type', 'payload', 'occurred_at'];

    protected $casts = ['payload' => 'array', 'occurred_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The plain-language ledger line the memory dashboard shows.
     */
    public function description(): string
    {
        $trail = $this->payload['trail_title'] ?? null;
        $artwork = $this->payload['artwork_title'] ?? null;

        return match ($this->event_type) {
            self::TRAIL_STARTED => "Started the trail “{$trail}”",
            self::TRAIL_REVISITED => "Returned to the trail “{$trail}”",
            self::TRAIL_ARCHIVED => "Archived the trail “{$trail}”",
            self::TRAIL_REACTIVATED => "Reactivated the trail “{$trail}”",
            self::TRAIL_DELETED => "Deleted the trail “{$trail}”",
            self::ARTWORK_SAVED_TO_TRAIL => "Saved “{$artwork}” to the trail “{$trail}”",
            self::TRAIL_ITEM_REMOVED => "Removed “{$artwork}” from the trail “{$trail}”",
            self::ARTWORK_SAVED => "Saved “{$artwork}” for later",
            self::SAVE_REMOVED => "Removed the save of “{$artwork}”",
            self::NOTE_WRITTEN => $trail ? "Wrote a note on the trail “{$trail}”" : 'Wrote a note',
            self::NOTE_DELETED => $trail ? "Deleted a note on the trail “{$trail}”" : 'Deleted a note',
            self::MEMORY_PAUSED => 'Paused memory',
            self::MEMORY_RESUMED => 'Resumed memory',
            self::CONSENT_RENEWED => 'Renewed the memory agreement',
            self::CONSENT_REVOKED => 'Revoked the memory agreement',
            self::MEMORY_DELETED => $this->memoryDeletedDescription(),
            self::SUGGESTION_DISMISSED => "Dismissed the suggestion of “{$artwork}” for the trail “{$trail}”",
            self::COMPARISON_REQUESTED => $this->comparisonRequestedDescription(),
            default => ucfirst(str_replace('_', ' ', $this->event_type)),
        };
    }

    private function comparisonRequestedDescription(): string
    {
        $titles = collect((array) ($this->payload['artwork_titles'] ?? []))->map(fn($title) => "“{$title}”");

        return $titles->isEmpty() ? 'Asked for a comparison' : 'Asked how ' . $titles->join(', ', ' and ') . ' compare';
    }

    /**
     * The deletion receipt in plain language: how much disappeared, nothing
     * about what it was.
     */
    private function memoryDeletedDescription(): string
    {
        $number = function (string $key, string $singular, string $plural): string {
            $count = (int) ($this->payload[$key] ?? 0);

            return $count === 1 ? "1 {$singular}" : "{$count} {$plural}";
        };

        return 'Deleted all memory held at the time — ' .
            collect([
                $number('trails', 'trail', 'trails'),
                $number('saves', 'save', 'saves'),
                $number('notes', 'note', 'notes'),
                $number('entries', 'earlier ledger entry', 'earlier ledger entries'),
            ])->join(', ', ' and ');
    }
}
