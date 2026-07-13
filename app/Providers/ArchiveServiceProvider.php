<?php

namespace App\Providers;

use RuntimeException;
use Illuminate\Http\Request;
use App\Services\Nexus\Fake\FakeNexus;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\Nexus\Contracts\NexusIndexer;
use App\Services\RedisIris\FakeRedisIrisMemory;
use App\Services\Nexus\Contracts\NexusRetriever;
use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * Binds the vendor boundaries. 'fake' is the default everywhere; 'live'
 * resolution fails loudly until real clients are implemented from official
 * vendor documentation (CLAUDE.md vendor API rule).
 */
class ArchiveServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Polite ceiling for NGA IIIF requests, shared by all queued
        // image-download jobs (see Jobs\Import\DownloadArtworkImage).
        RateLimiter::for('nga-iiif', function () {
            return Limit::perMinute(max(1, (int) config('nga.iiif.rate_limit_per_minute')));
        });

        // Anonymous visitors get limited, non-personalized semantic
        // exploration (CLAUDE.md consent model). Keyed per session — or per
        // user when logged in — never per IP, so shared networks are not
        // collectively punished.
        RateLimiter::for('archive-explore', function (Request $request) {
            $key =
                $request->user()?->id !== null
                    ? "user:{$request->user()->id}"
                    : "session:{$request->session()->getId()}";

            return Limit::perHour(max(1, (int) config('memory.anonymous_explore_rate_limit')))->by($key);
        });
    }

    public function register(): void
    {
        // One FakeNexus instance serves both sides of the boundary, so a
        // sync-queue test can index through NexusIndexer and assert
        // through the same fake.
        $this->app->singleton(FakeNexus::class);

        foreach ([NexusIndexer::class, NexusRetriever::class] as $contract) {
            $this->app->singleton($contract, function ($app) {
                return match (config('nexus.driver')) {
                    'fake' => $app->make(FakeNexus::class),
                    default => throw new RuntimeException(
                        'Nexus driver [' .
                            config('nexus.driver') .
                            '] is not implemented. ' .
                            'Only the fake driver exists until official Pinecone Nexus docs are provided.',
                    ),
                };
            });
        }

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
