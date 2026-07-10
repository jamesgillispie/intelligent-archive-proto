<?php

namespace Tests\Feature\Twill;

use Tests\TestCase;
use A17\Twill\Models\User;
use App\Models\Source\SourceImport;
use App\Twill\Capsules\Artworks\Models\Artwork;
use App\Twill\Capsules\Artworks\Models\ImageAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImportDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function twillUser(): User
    {
        return User::query()->forceCreate([
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'role' => 'SUPERADMIN',
            'published' => true,
            'registered_at' => now(),
        ]);
    }

    public function testGuestsAreRedirectedToTheAdminLogin(): void
    {
        $this->get(route('twill.importBatches.index'))->assertRedirect();
    }

    public function testEditorsSeeBatchCountsDurationsAndFlaggedImages(): void
    {
        SourceImport::factory()->create([
            'status' => SourceImport::STATUS_COMPLETED,
            'started_at' => now()->subMinutes(5),
            'finished_at' => now()->subMinutes(2),
            'report' => [
                'totals' => ['objects_selected' => 3],
                'source_records' => [
                    'objects' => ['created' => 3],
                    'objects_constituents' => ['created' => 4, 'duplicate_in_source' => 1],
                ],
            ],
        ]);

        $artwork = Artwork::factory()
            ->withPrimaryImage([
                'validation_status' => ImageAsset::VALIDATION_FAILED,
                'failure_reason' => 'http_404',
                'validated_at' => now(),
            ])
            ->create(['title' => 'Broken Download Painting']);

        $response = $this->actingAs($this->twillUser(), 'twill_users')->get(route('twill.importBatches.index'));

        $response->assertOk();
        $response->assertSee('Import batches');
        // Batch row: selected count, created sum, duplicate count, duration.
        $response->assertSee('completed');
        $response->assertSeeInOrder(['3', '7']);
        $response->assertSee('3 minutes');
        // Flagged image with a link to the artwork in the CMS.
        $response->assertSee('Broken Download Painting');
        $response->assertSee('http_404');
        $response->assertSee(route('twill.artworks.edit', $artwork->id), false);
    }
}
