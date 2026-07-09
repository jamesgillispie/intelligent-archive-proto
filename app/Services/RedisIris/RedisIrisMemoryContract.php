<?php

namespace App\Services\RedisIris;

/**
 * Consent-based visitor memory boundary (Redis Iris).
 *
 * Vendor API rule (CLAUDE.md): interface-level only; no vendor request or
 * response shapes until official docs are provided. Deliberately minimal
 * and vendor-neutral: namespaced context in, context out, plus the two
 * operations the consent model cannot exist without — inspection and purge.
 *
 * Iris is never the durable record: everything stored through this
 * boundary must be derived from SQL and rebuildable from it.
 */
interface RedisIrisMemoryContract
{
    /**
     * Store derived context under a namespace, e.g. "user:7:active_trail".
     *
     * @param  array<string, mixed>  $context
     */
    public function put(string $namespace, array $context, ?int $ttlSeconds = null): void;

    /**
     * @return array<string, mixed>|null
     */
    public function get(string $namespace): ?array;

    public function forget(string $namespace): void;

    /**
     * Everything held under a prefix (e.g. "user:7:"), verbatim — powers
     * the memory dashboard's "what the system currently holds" view.
     *
     * @return array<string, array<string, mixed>>
     */
    public function inspect(string $prefix): array;

    /**
     * Remove everything under a prefix. Used by the deletion flow; callers
     * verify with inspect() afterwards.
     */
    public function purge(string $prefix): void;
}
