<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Services\Memory\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Regression guards for two starter-kit interactions called out in
 * docs/PHASE-3-AUTH-PLAN.md: the Pages `/{slug}` catch-all must not shadow
 * auth routes, and auth/consented responses must never carry the public
 * response-cache headers the archive pages use.
 */
class RoutePrecedenceAndCachingTest extends TestCase
{
    use RefreshDatabase;

    public function testAuthRoutesWinOverThePagesCatchAll(): void
    {
        // If the catch-all resolved first, these would 404 (no Twill page
        // exists with these slugs) instead of rendering the auth screens.
        $this->get('/login')->assertOk()->assertSee('Sign in');
        $this->get('/register')->assertOk()->assertSee('Create an account');
        $this->get('/forgot-password')->assertOk()->assertSee('Reset your password');
    }

    public function testAuthPagesAreNeverPubliclyCacheable(): void
    {
        foreach (['/login', '/register', '/forgot-password'] as $uri) {
            $response = $this->get($uri);

            $this->assertStringNotContainsString(
                'public',
                (string) $response->headers->get('Cache-Control'),
                "$uri must not be publicly cacheable",
            );
        }
    }

    public function testConsentedPagesAreNeverPubliclyCacheable(): void
    {
        $user = User::factory()->create();
        app(ConsentService::class)->grant($user);

        $response = $this->actingAs($user)->get(route('account.trails'));

        $this->assertStringNotContainsString('public', (string) $response->headers->get('Cache-Control'));
    }

    public function testArchivePagesRemainPubliclyCacheableControl(): void
    {
        // Control: the caching mechanism this suite guards against is real
        // and still active on the anonymous archive. (X-Cacheable is an
        // internal marker CleanCacheableResponses strips after use, so the
        // observable contract is Cache-Control plus the absence of cookies.)
        $response = $this->get(route('archive.index'));

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('s-maxage', $cacheControl);
        $response->assertHeaderMissing('Set-Cookie');
    }
}
