<?php

namespace App\Console\Commands;

use RuntimeException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Services\Nga\NgaImportService;
use App\Services\Support\EnvironmentValidationService;

/**
 * Snapshot, don't stream (STRATEGY §2): every import reads CSVs pinned to
 * one upstream commit SHA under storage/app/nga/snapshots/{sha}/, so
 * re-running the same SHA is a no-op and a new SHA is an explicit refresh.
 * The dataset is public and CC0 — no secrets are involved anywhere here.
 */
class FetchNgaSnapshot extends Command
{
    protected $signature = 'nga:fetch
        {--sha= : Upstream commit SHA to pin (default: resolve the branch head)}
        {--force : Re-download files that already exist in the snapshot directory}';

    protected $description = 'Download the NGA Open Data CSV set at a pinned upstream commit SHA';

    public function handle(EnvironmentValidationService $environment): int
    {
        $environment->validate(['base', 'nga-fetch']);

        try {
            $sha = (string) ($this->option('sha') ?: $this->resolveHeadSha());
            $directory = rtrim((string) config('nga.fetch.snapshots_path'), '/') . "/{$sha}";

            File::ensureDirectoryExists($directory);

            $rows = [];

            foreach (NgaImportService::FILES as $file) {
                $rows[] = [$file, ...$this->fetchFile($sha, $file, $directory)];
            }

            $this->writeManifest($sha, $directory);
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Snapshot pinned to upstream commit {$sha}.");
        $this->table(['File', 'Bytes', 'Status'], $rows);
        $this->comment("Import it with: php artisan nga:import --path={$directory}");

        return self::SUCCESS;
    }

    /**
     * Head commit of the configured branch, via the public GitHub API.
     */
    protected function resolveHeadSha(): string
    {
        $repo = config('nga.fetch.repo');
        $branch = config('nga.fetch.branch');
        $url = rtrim((string) config('nga.fetch.api_base_url'), '/') . "/repos/{$repo}/commits/{$branch}";

        $sha = Http::timeout(30)->retry(2, 1000)->acceptJson()->get($url)->throw()->json('sha');

        if (!is_string($sha) || $sha === '') {
            throw new RuntimeException("Could not resolve the head commit of {$repo}@{$branch} from {$url}.");
        }

        return $sha;
    }

    /**
     * Stream one CSV to disk. A partial download never lands under its
     * final name, so a snapshot directory only ever holds complete files.
     *
     * @return array{0: int, 1: string} bytes written / status
     */
    protected function fetchFile(string $sha, string $file, string $directory): array
    {
        $target = "{$directory}/{$file}";

        if (!$this->option('force') && File::exists($target)) {
            return [(int) File::size($target), 'kept (already present)'];
        }

        $repo = config('nga.fetch.repo');
        $dataDir = trim((string) config('nga.fetch.data_dir'), '/');
        $url = rtrim((string) config('nga.fetch.raw_base_url'), '/') . "/{$repo}/{$sha}/{$dataDir}/{$file}";

        // throw: false so a failed status reaches the loud, URL-carrying
        // error below instead of a generic client exception.
        $response = Http::timeout((int) config('nga.fetch.timeout'))
            ->retry(2, 500, throw: false)
            ->withOptions(['stream' => true])
            ->get($url);

        if (!$response->successful()) {
            throw new RuntimeException("Download failed for {$url} (HTTP {$response->status()}).");
        }

        $partial = "{$target}.partial";
        $body = $response->toPsrResponse()->getBody();

        // Real streamed bodies are not seekable and arrive unread; already
        // buffered ones (fakes, small responses) need their pointer reset.
        if ($body->isSeekable()) {
            $body->rewind();
        }

        $handle = fopen($partial, 'wb');

        if ($handle === false) {
            throw new RuntimeException("Could not open {$partial} for writing.");
        }

        try {
            while (!$body->eof()) {
                fwrite($handle, $body->read(1024 * 1024));
            }
        } finally {
            fclose($handle);
        }

        File::move($partial, $target);

        return [(int) File::size($target), 'downloaded'];
    }

    protected function writeManifest(string $sha, string $directory): void
    {
        $files = [];

        foreach (NgaImportService::FILES as $file) {
            $files[$file] = (int) File::size("{$directory}/{$file}");
        }

        File::put(
            "{$directory}/snapshot.json",
            (string) json_encode(
                [
                    'sha' => $sha,
                    'repo' => config('nga.fetch.repo'),
                    'branch' => config('nga.fetch.branch'),
                    'fetched_at' => now()->toIso8601String(),
                    'files' => $files,
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
            ),
        );
    }
}
