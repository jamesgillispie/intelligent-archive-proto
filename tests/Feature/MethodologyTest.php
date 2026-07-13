<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * The methodology page is the public record of the guarantees the rest of
 * this suite proves. It must always resolve (it is linked from the consent
 * banner shown at registration) and must carry the load-bearing statements.
 */
class MethodologyTest extends TestCase
{
    use RefreshDatabase;

    public function testItRendersAnonymouslyWithTheGuarantees(): void
    {
        $this->get(route('methodology'))
            ->assertOk()
            ->assertSee('How memory works')
            // The whole list of enumerated inputs, and the claim that it is whole.
            ->assertSee('dismissing a suggestion')
            ->assertSee('whole list.')
            ->assertSee('no background profiling')
            // Visitor-blindness of the knowledge layer.
            ->assertSee('No account, no name, no history crosses that boundary')
            ->assertSee('by design, not by cleanup')
            // The suggestion record and citation discipline.
            ->assertSee('written down before it is shown')
            ->assertSee('suggested to you again, anywhere.')
            ->assertSee('Citations over vibes')
            // The standing rights.
            ->assertSee('Pause, export, delete')
            ->assertSee('purges the working context')
            ->assertSee('Memory agreement ' . config('memory.consent_version'));
    }

    public function testItIsPubliclyCacheable(): void
    {
        $response = $this->get(route('methodology'))->assertOk();

        // X-Cacheable is an internal flag stripped after cookie cleaning;
        // the observable contract is the Cache-Control header.
        $cacheControl = (string) $response->headers->get('Cache-Control');

        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('s-maxage=3600', $cacheControl);
    }
}
