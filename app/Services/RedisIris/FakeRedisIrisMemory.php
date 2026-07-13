<?php

namespace App\Services\RedisIris;

use Illuminate\Support\Facades\Cache;

/**
 * Stand-in for Redis Iris, backed by the application cache. Honors TTLs
 * and the full inspect/purge lifecycle so consent flows are testable and
 * demoable end-to-end with no vendor dependency: the array store keeps
 * tests hermetic, while the local file/database cache lets held context
 * survive across requests — so the dashboard's "what the system currently
 * holds" panel shows something real. Exactly as disposable as Iris context
 * must be: SQL remains the durable record.
 */
class FakeRedisIrisMemory implements RedisIrisMemoryContract
{
    public function put(string $namespace, array $context, ?int $ttlSeconds = null): void
    {
        $ttlSeconds ??= (int) config('redis_iris.default_ttl');

        // 0 means no expiry; negative TTLs are already expired (the cache
        // treats them as a delete).
        Cache::put($this->key($namespace), $context, $ttlSeconds === 0 ? null : $ttlSeconds);

        $index = $this->index();

        if (Cache::has($this->key($namespace))) {
            $index[$namespace] = true;
        } else {
            unset($index[$namespace]);
        }

        $this->saveIndex($index);
    }

    public function get(string $namespace): ?array
    {
        $context = Cache::get($this->key($namespace));

        return is_array($context) ? $context : null;
    }

    public function forget(string $namespace): void
    {
        Cache::forget($this->key($namespace));

        $index = $this->index();
        unset($index[$namespace]);
        $this->saveIndex($index);
    }

    public function inspect(string $prefix): array
    {
        $held = [];
        $index = $this->index();

        foreach (array_keys($index) as $namespace) {
            if (!str_starts_with($namespace, $prefix)) {
                continue;
            }

            $context = $this->get($namespace);

            if ($context === null) {
                // Expired since it was indexed — prune quietly.
                unset($index[$namespace]);

                continue;
            }

            $held[$namespace] = $context;
        }

        $this->saveIndex($index);

        return $held;
    }

    public function purge(string $prefix): void
    {
        $index = $this->index();

        foreach (array_keys($index) as $namespace) {
            if (str_starts_with($namespace, $prefix)) {
                Cache::forget($this->key($namespace));
                unset($index[$namespace]);
            }
        }

        $this->saveIndex($index);
    }

    protected function key(string $namespace): string
    {
        return config('redis_iris.key_prefix') . ':' . $namespace;
    }

    /**
     * Known namespaces, so inspect/purge can enumerate by prefix — the one
     * piece of bookkeeping a key-value cache doesn't give us.
     *
     * @return array<string, true>
     */
    protected function index(): array
    {
        $index = Cache::get($this->key('__namespaces'));

        return is_array($index) ? $index : [];
    }

    /**
     * @param  array<string, true>  $index
     */
    protected function saveIndex(array $index): void
    {
        Cache::forever($this->key('__namespaces'), $index);
    }
}
