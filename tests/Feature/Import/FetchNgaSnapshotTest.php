<?php

namespace Tests\Feature\Import;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Services\Nga\NgaImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FetchNgaSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected string $snapshotsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->snapshotsPath = storage_path('framework/testing/nga-snapshots');
        config(['nga.fetch.snapshots_path' => $this->snapshotsPath]);
        File::deleteDirectory($this->snapshotsPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->snapshotsPath);

        parent::tearDown();
    }

    public function testFetchPinsASnapshotToTheBranchHead(): void
    {
        Http::fake([
            'api.github.com/repos/NationalGalleryOfArt/opendata/commits/main' => Http::response(['sha' => 'abc123']),
            'raw.githubusercontent.com/NationalGalleryOfArt/opendata/abc123/data/*' => Http::response("header\nrow\n"),
        ]);

        $this->artisan('nga:fetch')->assertSuccessful()->expectsOutputToContain('abc123');

        $directory = "{$this->snapshotsPath}/abc123";

        foreach (NgaImportService::FILES as $file) {
            $this->assertFileExists("{$directory}/{$file}");
            $this->assertSame("header\nrow\n", File::get("{$directory}/{$file}"));
        }

        $manifest = json_decode(File::get("{$directory}/snapshot.json"), true);

        $this->assertSame('abc123', $manifest['sha']);
        $this->assertSame('NationalGalleryOfArt/opendata', $manifest['repo']);
        $this->assertArrayHasKey('objects.csv', $manifest['files']);
    }

    public function testRerunningTheSameShaIsANoOpForExistingFiles(): void
    {
        Http::fake(['raw.githubusercontent.com/*' => Http::response('fresh')]);

        $directory = "{$this->snapshotsPath}/pinned1";
        File::ensureDirectoryExists($directory);
        File::put("{$directory}/objects.csv", 'original');

        $this->artisan('nga:fetch', ['--sha' => 'pinned1'])->assertSuccessful();

        // The existing file was kept; only the other three were fetched.
        $this->assertSame('original', File::get("{$directory}/objects.csv"));
        Http::assertSentCount(3);

        foreach (NgaImportService::FILES as $file) {
            $this->assertFileExists("{$directory}/{$file}");
        }
    }

    public function testAFailedDownloadFailsLoudlyAndLeavesNoPartialFile(): void
    {
        Http::fake(['raw.githubusercontent.com/*' => Http::response('', 404)]);

        $this->artisan('nga:fetch', ['--sha' => 'deadbeef'])
            ->assertFailed()
            ->expectsOutputToContain('HTTP 404');

        $directory = "{$this->snapshotsPath}/deadbeef";

        $this->assertFileDoesNotExist("{$directory}/objects.csv");
        $this->assertFileDoesNotExist("{$directory}/objects.csv.partial");
        $this->assertFileDoesNotExist("{$directory}/snapshot.json");
    }

    public function testTheImportReportRecordsThePinnedSha(): void
    {
        // Written by nga:fetch, read by nga:import: the provenance chain
        // from upstream commit to source rows.
        $directory = "{$this->snapshotsPath}/prov42";
        File::ensureDirectoryExists($directory);
        File::copyDirectory(base_path('tests/Fixtures/nga'), $directory);
        File::put(
            "{$directory}/snapshot.json",
            (string) json_encode(['sha' => 'prov42', 'fetched_at' => '2026-07-13T00:00:00+00:00']),
        );

        $this->artisan('nga:import', [
            '--path' => $directory,
            '--limit' => 1,
            '--sync' => true,
            '--skip-images' => true,
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('upstream commit prov42');
    }
}
