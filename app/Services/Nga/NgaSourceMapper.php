<?php

namespace App\Services\Nga;

use Illuminate\Support\Carbon;
use App\Models\Source\SourceImport;
use App\Models\Source\SourceRecord;

/**
 * Writes raw NGA rows into the source layer with change detection.
 *
 * Idempotent: unchanged rows only touch last_seen_at; changed rows refresh
 * the payload and bump version; nothing is ever deleted here.
 */
class NgaSourceMapper
{
    /** @var array<string, array<string, int>> table => [created|updated|unchanged => n] */
    protected array $counts = [];

    /**
     * @param  array<string, string>  $payload raw CSV row, verbatim
     */
    public function record(SourceImport $import, string $sourceTable, string $sourceId, array $payload): SourceRecord
    {
        $hash = $this->hash($payload);
        $now = now();

        $record = SourceRecord::query()->forTable($sourceTable)->where('source_id', $sourceId)->first();

        if ($record === null) {
            $this->count($sourceTable, 'created');

            return SourceRecord::create([
                'source_import_id' => $import->id,
                'source_table' => $sourceTable,
                'source_id' => $sourceId,
                'payload' => $payload,
                'content_hash' => $hash,
                'version' => 1,
                'rights' => $this->rightsFor($sourceTable, $payload),
                'source_modified_at' => $this->sourceModifiedAt($payload),
                'first_seen_at' => $now,
                'last_seen_at' => $now,
            ]);
        }

        if ($record->content_hash === $hash) {
            $record->forceFill(['last_seen_at' => $now, 'missing_from_source_at' => null])->save();
            $this->count($sourceTable, 'unchanged');

            return $record;
        }

        $record
            ->forceFill([
                'source_import_id' => $import->id,
                'payload' => $payload,
                'content_hash' => $hash,
                'version' => $record->version + 1,
                'rights' => $this->rightsFor($sourceTable, $payload),
                'source_modified_at' => $this->sourceModifiedAt($payload),
                'last_seen_at' => $now,
                'missing_from_source_at' => null,
            ])
            ->save();

        $this->count($sourceTable, 'updated');

        return $record;
    }

    /**
     * Stable content hash for change detection.
     *
     * @param  array<string, string>  $payload
     */
    public function hash(array $payload): string
    {
        ksort($payload);

        return hash('sha256', (string) json_encode($payload));
    }

    /**
     * Per-table created/updated/unchanged counters for this run. Counts
     * only — record contents never appear in logs or command output.
     *
     * @return array<string, array<string, int>>
     */
    public function counts(): array
    {
        return $this->counts;
    }

    public function resetCounts(): void
    {
        $this->counts = [];
    }

    protected function count(string $sourceTable, string $result): void
    {
        $this->counts[$sourceTable][$result] = ($this->counts[$sourceTable][$result] ?? 0) + 1;
    }

    /**
     * Rights signal: NGA publishes IIIF imagery only for open-access works,
     * so a published_images row implies CC0 open access.
     *
     * @param  array<string, string>  $payload
     */
    protected function rightsFor(string $sourceTable, array $payload): ?string
    {
        if ($sourceTable === SourceRecord::TABLE_PUBLISHED_IMAGES) {
            return 'CC0 (NGA open access image program)';
        }

        return null;
    }

    /**
     * @param  array<string, string>  $payload
     */
    protected function sourceModifiedAt(array $payload): ?Carbon
    {
        $value = $payload['lastdetectedmodification'] ?? null;

        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
