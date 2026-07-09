<?php

namespace App\Models\Source;

use Illuminate\Database\Eloquent\Model;
use Database\Factories\Source\SourceImportFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * One NGA Open Data import run — the provenance anchor for every source
 * record and, transitively, every canonical archive record.
 *
 * @mixin IdeHelperSourceImport
 */
class SourceImport extends Model
{
    /** @use HasFactory<SourceImportFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = ['snapshot_ref', 'data_path', 'status', 'report', 'started_at', 'finished_at'];

    protected $casts = [
        'report' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    protected static function newFactory(): SourceImportFactory
    {
        return SourceImportFactory::new();
    }

    public function records(): HasMany
    {
        return $this->hasMany(SourceRecord::class);
    }
}
