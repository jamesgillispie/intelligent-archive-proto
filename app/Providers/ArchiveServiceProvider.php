<?php

namespace App\Providers;

use RuntimeException;
use Illuminate\Support\ServiceProvider;
use App\Services\Nexus\FakeNexusArchive;
use App\Services\Nexus\NexusArchiveContract;
use App\Services\RedisIris\FakeRedisIrisMemory;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Binds the vendor boundaries. 'fake' is the default everywhere; 'live'
 * resolution fails loudly until real clients are implemented from official
 * vendor documentation (CLAUDE.md vendor API rule).
 */
class ArchiveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NexusArchiveContract::class, function () {
            return match (config('nexus.driver')) {
                'fake' => new FakeNexusArchive(),
                default => throw new RuntimeException(
                    'Nexus driver [' .
                        config('nexus.driver') .
                        '] is not implemented. ' .
                        'Only the fake driver exists until official Pinecone Nexus docs are provided.',
                ),
            };
        });

        $this->app->singleton(RedisIrisMemoryContract::class, function () {
            return match (config('redis_iris.driver')) {
                'fake' => new FakeRedisIrisMemory(),
                default => throw new RuntimeException(
                    'Redis Iris driver [' .
                        config('redis_iris.driver') .
                        '] is not implemented. ' .
                        'Only the fake driver exists until official Redis Iris docs are provided.',
                ),
            };
        });
    }
}
