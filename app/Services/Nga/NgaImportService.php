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
    public function run(?int $limit = null, ?string $path = null, bool $queueCanonicalization = true): array
    {
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
            $report = $this->execute($import, $path, $limit, $queueCanonicalization);
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
    protected function execute(SourceImport $import, string $path, int $limit, bool $queue): array
    {
        // Pass 1: object ids that have a primary published image.
        $withPrimaryImage = [];

        foreach ($this->reader->rows("{$path}/published_images.csv") as $row) {
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

            if ($objectId === '' || !isset($selected[$objectId]) || ($row['roletype'] ?? '') !== 'artist') {
                continue;
            }

            $constituentId = $row['constituentid'] ?? '';

            if ($constituentId === '') {
                continue;
            }

            $constituentIds[$constituentId] = true;

            $this->mapper->record(
                $import,
                SourceRecord::TABLE_OBJECTS_CONSTITUENTS,
                "{$objectId}:{$constituentId}",
                $row,
            );
        }

        // Pass 5: constituents for the selected joins.
        foreach ($this->reader->rows("{$path}/constituents.csv") as $row) {
            $constituentId = $row['constituentid'] ?? '';

            if ($constituentId !== '' && isset($constituentIds[$constituentId])) {
                $this->mapper->record($import, SourceRecord::TABLE_CONSTITUENTS, $constituentId, $row);
            }
        }

        // Canonicalize: queue-friendly, one job per object.
        foreach (array_keys($selected) as $objectId) {
            $job = new CanonicalizeArtwork((string) $objectId);

            $queue ? dispatch($job) : dispatch_sync($job);
        }

        return [
            'totals' => [
                'objects_seen' => $objectsSeen,
                'objects_eligible' => $eligible,
                'objects_selected' => count($selected),
                'constituents_selected' => count($constituentIds),
                'canonicalization_jobs' => count($selected),
            ],
            'source_records' => $this->mapper->counts(),
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
