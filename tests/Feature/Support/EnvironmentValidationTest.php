<?php

namespace Tests\Feature\Support;

use Tests\TestCase;
use App\Services\Support\EnvironmentValidationService;
use App\Services\Support\EnvironmentValidationException;

class EnvironmentValidationTest extends TestCase
{
    public function testCommandPassesWithPlaceholderSafeDefaults(): void
    {
        $this->artisan('env:validate')->assertSuccessful()->expectsOutputToContain('required variables present');
    }

    public function testCommandFailsNamingTheMissingVariableOnly(): void
    {
        config(['nexus.driver' => 'live', 'nexus.api_key' => null, 'nexus.endpoint' => null]);

        $this->artisan('env:validate')->assertFailed()->expectsOutputToContain('NEXUS_API_KEY');
    }

    public function testLiveDriversAddTheirContextsAtRuntime(): void
    {
        $service = app(EnvironmentValidationService::class);

        $this->assertNotContains('nexus-live', $service->runtimeContexts());

        config(['nexus.driver' => 'live', 'redis_iris.driver' => 'live']);

        $this->assertContains('nexus-live', $service->runtimeContexts());
        $this->assertContains('redis-iris-live', $service->runtimeContexts());
    }

    public function testServiceThrowsWithVariableNamesNeverValues(): void
    {
        config([
            'redis_iris.driver' => 'live',
            'redis_iris.url' => null,
            'redis_iris.api_key' => 'fake-value-should-not-leak',
        ]);

        $service = app(EnvironmentValidationService::class);

        try {
            $service->validate($service->runtimeContexts());
            $this->fail('Expected EnvironmentValidationException');
        } catch (EnvironmentValidationException $exception) {
            $this->assertStringContainsString('REDIS_IRIS_URL', $exception->getMessage());
            $this->assertStringNotContainsString('fake-value-should-not-leak', $exception->getMessage());
        }
    }

    public function testUnknownContextIsRejected(): void
    {
        $this->expectException(EnvironmentValidationException::class);

        app(EnvironmentValidationService::class)->missing(['nope']);
    }
}
