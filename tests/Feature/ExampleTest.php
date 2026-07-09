<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The archive landing page renders without any CMS content, unlike `/`,
     * which requires a homepage singleton to exist.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/archive');

        $response->assertStatus(200);
    }
}
