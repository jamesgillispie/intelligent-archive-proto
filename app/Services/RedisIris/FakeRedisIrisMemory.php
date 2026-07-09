<?php

namespace App\Services\RedisIris;

/**
 * In-process stand-in for Redis Iris. Honors TTLs and the full
 * inspect/purge lifecycle so consent flows are testable end-to-end with no
 * vendor dependency.
 */
class FakeRedisIrisMemory implements RedisIrisMemoryContract
{
    /** @var array<string, array{context: array<string, mixed>, expires_at: ?int}> */
    protected array $store = [];

    public function put(string $namespace, array $context, ?int $ttlSeconds = null): void
    {
        $ttlSeconds ??= (int) config('redis_iris.default_ttl');

        $this->store[$this->key($namespace)] = [
            'context' => $context,
            // 0 means no expiry; negative TTLs are already expired.
            'expires_at' => $ttlSeconds === 0 ? null : time() + $ttlSeconds,
        ];
    }

    public function get(string $namespace): ?array
    {
        $key = $this->key($namespace);
        $entry = $this->store[$key] ?? null;

        if ($entry === null) {
            return null;
        }

        if ($entry['expires_at'] !== null && $entry['expires_at'] < time()) {
            unset($this->store[$key]);

            return null;
        }

        return $entry['context'];
    }

    public function forget(string $namespace): void
    {
        unset($this->store[$this->key($namespace)]);
    }

    public function inspect(string $prefix): array
    {
        $keyPrefix = $this->key($prefix);
        $held = [];

        foreach ($this->store as $key => $entry) {
            if (str_starts_with($key, $keyPrefix)) {
                $held[$key] = $entry['context'];
            }
        }

        return $held;
    }

    public function purge(string $prefix): void
    {
        $keyPrefix = $this->key($prefix);

        foreach (array_keys($this->store) as $key) {
            if (str_starts_with($key, $keyPrefix)) {
                unset($this->store[$key]);
            }
        }
    }

    protected function key(string $namespace): string
    {
        return config('redis_iris.key_prefix') . ':' . $namespace;
    }
}
