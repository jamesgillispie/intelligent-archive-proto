<?php

namespace App\Models\Source;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\Source\SourceRecordFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * One raw NGA Open Data row, preserved verbatim in `payload`.
 *
 * Immutable by convention: only the import pipeline writes here (updates are
 * limited to re-import bookkeeping: payload refresh + version bump on
 * upstream change, last_seen_at, missing_from_source_at). No Twill module
 * manages this layer, and canonical models never edit it.
 *
 * @mixin IdeHelperSourceRecord
 */
class SourceRecord extends Model
{
    /** @use HasFactory<SourceRecordFactory> */
    use HasFactory;

    public const TABLE_OBJECTS = 'objects';
    public const TABLE_CONSTITUENTS = 'constituents';
    public const TABLE_OBJECTS_CONSTITUENTS = 'objects_constituents';
    public const TABLE_PUBLISHED_IMAGES = 'published_images';

    protected $fillable = [
        'source_import_id',
        'source_table',
        'source_id',
        'payload',
        'content_hash',
        'version',
        'rights',
        'source_modified_at',
        'first_seen_at',
        'last_seen_at',
        'missing_from_source_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'version' => 'integer',
        'source_modified_at' => 'datetime',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'missing_from_source_at' => 'datetime',
    ];

    protected static function newFactory(): SourceRecordFactory
    {
        return SourceRecordFactory::new();
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(SourceImport::class, 'source_import_id');
    }

    public function scopeForTable(Builder $query, string $sourceTable): Builder
    {
        return $query->where('source_table', $sourceTable);
    }

    public function scopePresentInSource(Builder $query): Builder
    {
        return $query->whereNull('missing_from_source_at');
    }

    /**
     * Read a value from the raw payload without losing the original.
     */
    public function payloadValue(string $key): ?string
    {
        $value = $this->payload[$key] ?? null;

        return $value === '' ? null : $value;
    }
}
