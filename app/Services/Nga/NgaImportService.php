<?php

namespace App\Services\Nga;

use RuntimeException;
use App\Models\Source\SourceImport;
use App\Models\Source\SourceRecord;
use Illuminate\Support\Facades\Log;
use App\Jobs\Import\CanonicalizeArtwork;
use App\Services\Support\EnvironmentValidationService;

/**
 * Orchestrates a curated-subset NGA Open Data import.
 *
 * Source records are written first (raw preservation), then each selected
 * object is canonicalized via a queue-friendly job. Repeatable and
 * idempotent: re-running against the same files changes nothing but
 * last_seen_at. All reporting is counts only — record contents never reach
 * logs or command output.
 */
class NgaImportService
{
    public const FILES = [
        'objects' => 'objects.csv',
        'constituents' => 'constituents.csv',
        'objects_constituents' => 'objects_constituents.csv',
        'published_images' => 'published_images.csv',
    ];

    public function __construct(
        protected NgaCsvReader $reader,
        protected NgaSourceMapper $mapper,
        protected EnvironmentValidationService $environment,
    ) {}

    /**
     * @return array<string, mixed> count report
     */
    public function run(
        ?int $limit = null,
        ?string $path = null,
        bool $queueCanonicalization = true,
        bool $downloadImages = true,
    ): array {
        $this->environment->validate(['base', 'nga-import']);

        $path = rtrim($path ?: config('nga.open_data_path'), '/');
        $limit = $limit ?? (int) config('nga.import.limit');

        $this->assertFilesExist($path);

        $import = SourceImport::create([
            'data_path' => $path,
            'status' => SourceImport::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        $this->mapper->resetCounts();

        try {
            $report = $this->execute($import, $path, $limit, $queueCanonicalization, $downloadImages);
        } catch (\Throwable $exception) {
            $import->update(['status' => SourceImport::STATUS_FAILED, 'finished_at' => now()]);

            throw $exception;
        }

        $import->update([
            'status' => SourceImport::STATUS_COMPLETED,
            'finished_at' => now(),
            'report' => $report,
        ]);

        Log::info('NGA import completed', ['source_import_id' => $import->id] + $report['totals']);

        return $report;
    }

    /**
     * @return array<string, mixed>
     */
    protected function execute(SourceImport $import, string $path, int $limit, bool $queue, bool $downloadImages): array
    {
        // Sweep bookkeeping: which of the source rows we already hold
        // appear anywhere in this snapshot, selected or not. Absence from
        // the files — never absence from the curated subset — is what
        // "missing from source" means.
        $known = $this->knownSourceIds();
        $present = [];

        // Pass 1: object ids that have a primary published image.
        $withPrimaryImage = [];

        foreach ($this->reader->rows("{$path}/published_images.csv") as $row) {
            $this->markPresent($present, $known, SourceRecord::TABLE_PUBLISHED_IMAGES, $row['uuid'] ?? '');

            if (($row['viewtype'] ?? '') === 'primary' && ($row['depictstmsobjectid'] ?? '') !== '') {
                $withPrimaryImage[$row['depictstmsobjectid']] = true;
            }
        }

        // Pass 2: select the curated subset from objects.csv.
        $classification = (string) config('nga.import.classification');
        $accessionedOnly = (bool) config('nga.import.accessioned_only');
        $requireImage = (bool) config('nga.import.require_primary_image');

        $selected = [];
        $objectsSeen = 0;
        $eligible = 0;

        foreach ($this->reader->rows("{$path}/objects.csv") as $row) {
            $objectsSeen++;

            $this->markPresent($present, $known, SourceRecord::TABLE_OBJECTS, $row['objectid'] ?? '');

            if (!$this->isEligible($row, $classification, $accessionedOnly, $requireImage, $withPrimaryImage)) {
                continue;
            }

            $eligible++;

            if (count($selected) < $limit) {
                $selected[$row['objectid']] = $row;
            }
        }

        // Write object source records.
        foreach ($selected as $objectId => $row) {
            $this->mapper->record($import, SourceRecord::TABLE_OBJECTS, (string) $objectId, $row);
        }

        // Pass 3: image rows for selected objects.
        foreach ($this->reader->rows("{$path}/published_images.csv") as $row) {
            $objectId = $row['depictstmsobjectid'] ?? '';

            if ($objectId !== '' && isset($selected[$objectId]) && ($row['uuid'] ?? '') !== '') {
                $this->mapper->record($import, SourceRecord::TABLE_PUBLISHED_IMAGES, $row['uuid'], $row);
            }
        }

        // Pass 4: object↔constituent joins (artist roles only).
        $constituentIds = [];

        foreach ($this->reader->rows("{$path}/objects_constituents.csv") as $row) {
            $objectId = $row['objectid'] ?? '';
            $constituentId = $row['constituentid'] ?? '';

            if ($objectId === '' || $constituentId === '') {
                continue;
            }

            // Role is part of the key: NGA ships multiple join rows for the
            // same (object, constituent) pair that differ only in role text.
            // Keying by pair alone made those rows fight over one record and
            // report "updated" on every run.
            $role = trim($row['role'] ?? '');
            $joinKey = "{$objectId}:{$constituentId}:{$role}";

            $this->markPresent($present, $known, SourceRecord::TABLE_OBJECTS_CONSTITUENTS, $joinKey);

            if (!isset($selected[$objectId]) || ($row['roletype'] ?? '') !== 'artist') {
                continue;
            }

            $constituentIds[$constituentId] = true;

            $this->mapper->record($import, SourceRecord::TABLE_OBJECTS_CONSTITUENTS, $joinKey, $row);
        }

        // Pass 5: constituents for the selected joins.
        foreach ($this->reader->rows("{$path}/constituents.csv") as $row) {
            $constituentId = $row['constituentid'] ?? '';

            $this->markPresent($present, $known, SourceRecord::TABLE_CONSTITUENTS, $constituentId);

            if ($constituentId !== '' && isset($constituentIds[$constituentId])) {
                $this->mapper->record($import, SourceRecord::TABLE_CONSTITUENTS, $constituentId, $row);
            }
        }

        // Canonicalize: queue-friendly, one job per object.
        foreach (array_keys($selected) as $objectId) {
            $job = new CanonicalizeArtwork((string) $objectId, $downloadImages);

            $queue ? dispatch($job) : dispatch_sync($job);
        }

        $report = [
            'totals' => [
                'objects_seen' => $objectsSeen,
                'objects_eligible' => $eligible,
                'objects_selected' => count($selected),
                'constituents_selected' => count($constituentIds),
                'canonicalization_jobs' => count($selected),
            ],
            'source_records' => $this->mapper->counts(),
            'missing_sweep' => $this->sweepMissing($known, $present),
        ];

        $snapshot = $this->snapshotManifest($path);

        if ($snapshot !== null) {
            $report['snapshot'] = $snapshot;
        }

        return $report;
    }

    /**
     * Source ids already held per table, so the sweep can tell "absent
     * from this snapshot" apart from "never imported".
     *
     * @return array<string, array<string|int, true>>
     */
    protected function knownSourceIds(): array
    {
        $known = [];

        foreach (array_keys(self::FILES) as $table) {
            $ids = SourceRecord::forTable($table)->pluck('source_id')->all();

            $known[$table] = $ids === [] ? [] : array_fill_keys($ids, true);
        }

        return $known;
    }

    /**
     * @param  array<string, array<string|int, true>>  $present
     * @param  array<string, array<string|int, true>>  $known
     */
    protected function markPresent(array &$present, array $known, string $table, string $id): void
    {
        if ($id !== '' && isset($known[$table][$id])) {
            $present[$table][$id] = true;
        }
    }

    /**
     * Records absent from the snapshot are stamped missing_from_source_at;
     * previously-missing records that reappear are cleared, even when the
     * subset filter leaves them unselected. Nothing is ever deleted.
     *
     * @param  array<string, array<string|int, true>>  $known
     * @param  array<string, array<string|int, true>>  $present
     * @return array<string, array<string, int>>
     */
    protected function sweepMissing(array $known, array $present): array
    {
        $report = [];
        $now = now();

        foreach ($known as $table => $ids) {
            $missingIds = array_keys(array_diff_key($ids, $present[$table] ?? []));
            $presentIds = array_keys($present[$table] ?? []);

            $marked = 0;

            foreach (array_chunk($missingIds, 500) as $chunk) {
                $marked += SourceRecord::forTable($table)
                    ->whereIn('source_id', array_map('strval', $chunk))
                    ->whereNull('missing_from_source_at')
                    ->update(['missing_from_source_at' => $now]);
            }

            $reappeared = 0;

            foreach (array_chunk($presentIds, 500) as $chunk) {
                $reappeared += SourceRecord::forTable($table)
                    ->whereIn('source_id', array_map('strval', $chunk))
                    ->whereNotNull('missing_from_source_at')
                    ->update(['missing_from_source_at' => null]);
            }

            if ($marked > 0 || $reappeared > 0) {
                $report[$table] = ['marked_missing' => $marked, 'reappeared' => $reappeared];
            }
        }

        return $report;
    }

    /**
     * The snapshot.json manifest nga:fetch writes next to the CSVs — the
     * pinned upstream commit this import actually read.
     *
     * @return array<string, mixed>|null
     */
    protected function snapshotManifest(string $path): ?array
    {
        $file = "{$path}/snapshot.json";

        if (!is_readable($file)) {
            return null;
        }

        $manifest = json_decode((string) file_get_contents($file), true);

        if (!is_array($manifest)) {
            return null;
        }

        return [
            'sha' => $manifest['sha'] ?? null,
            'fetched_at' => $manifest['fetched_at'] ?? null,
        ];
    }

    /**
     * @param  array<string, string>  $row
     * @param  array<string, bool>  $withPrimaryImage
     */
    protected function isEligible(
        array $row,
        string $classification,
        bool $accessionedOnly,
        bool $requireImage,
        array $withPrimaryImage,
    ): bool {
        if (($row['objectid'] ?? '') === '') {
            return false;
        }

        if ($classification !== '' && ($row['classification'] ?? '') !== $classification) {
            return false;
        }

        if ($accessionedOnly && ($row['accessioned'] ?? '1') === '0') {
            return false;
        }

        if (($row['isvirtual'] ?? '0') === '1') {
            return false;
        }

        if ($requireImage && !isset($withPrimaryImage[$row['objectid']])) {
            return false;
        }

        return true;
    }

    protected function assertFilesExist(string $path): void
    {
        $missing = array_filter(self::FILES, fn(string $file) => !is_readable("{$path}/{$file}"));

        if ($missing !== []) {
            throw new RuntimeException(
                'NGA Open Data files missing under ' .
                    $path .
                    ': ' .
                    implode(', ', $missing) .
                    '. Download them from the NationalGalleryOfArt/opendata repository first.',
            );
        }
    }
}
