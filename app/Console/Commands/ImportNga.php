<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Nga\NgaImportService;

class ImportNga extends Command
{
    protected $signature = 'nga:import
        {--limit= : Maximum artworks to import (default: NGA_IMPORT_LIMIT)}
        {--path= : Directory containing the NGA CSV files (default: NGA_OPEN_DATA_PATH)}
        {--sync : Canonicalize inline instead of dispatching queue jobs}';

    protected $description = 'Import a curated NGA Open Data subset: source records first, then canonical artworks';

    public function handle(NgaImportService $import): int
    {
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        try {
            $report = $import->run(
                limit: $limit,
                path: $this->option('path') ?: null,
                queueCanonicalization: !$this->option('sync'),
            );
        } catch (\Throwable $exception) {
            // Counts and variable names only — never record contents.
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Import completed.');

        /** @var array<string, int> $totals */
        $totals = $report['totals'];

        /** @var array<string, array<string, int>> $sourceRecordCounts */
        $sourceRecordCounts = $report['source_records'];

        $this->table(
            ['Metric', 'Count'],
            collect($totals)
                ->map(fn(int $count, string $metric) => [str_replace('_', ' ', $metric), $count])
                ->values()
                ->all(),
        );

        $rows = [];

        foreach ($sourceRecordCounts as $table => $counts) {
            foreach ($counts as $result => $count) {
                $rows[] = ["{$table} {$result}", $count];
            }
        }

        if ($rows !== []) {
            $this->table(['Source records', 'Count'], $rows);
        }

        if (!$this->option('sync')) {
            $this->comment('Canonicalization dispatched to the queue; run a worker if QUEUE_CONNECTION is not sync.');
        }

        return self::SUCCESS;
    }
}
