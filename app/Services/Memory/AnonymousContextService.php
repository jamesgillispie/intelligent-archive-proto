<?php

namespace App\Services\Memory;

use App\Services\RedisIris\RedisIrisMemoryContract;

/**
 * The whole of what is held for an anonymous visitor: the last few
 * explore queries, keyed by session id only, TTL-capped, and never linked
 * to an account. Reading is free — the home page and the explore surface
 * both show the session's earlier queries — but writing happens only when
 * a query is actually made. Hard cap by design: this must never
 * accumulate into a profile.
 */
class AnonymousContextService
{
    public function __construct(
        private readonly RedisIrisMemoryContract $iris,
        private readonly VisitorContextService $visitors,
    ) {}

    /**
     * The session's earlier queries, newest first, excluding the one being
     * made right now.
     *
     * @return list<string>
     */
    public function recentQueries(string $sessionId, string $excluding = ''): array
    {
        $held = $this->iris->get($this->visitors->sessionNamespace($sessionId, 'anonymous_context'));

        return array_values(
            array_filter(
                (array) ($held['recent_queries'] ?? []),
                fn($previous) => is_string($previous) && $previous !== '' && $previous !== $excluding,
            ),
        );
    }

    /**
     * Hold the query at the head of the session's recent list, under the
     * hard TTL cap.
     */
    public function rememberQuery(string $sessionId, string $query): void
    {
        if ($query === '') {
            return;
        }

        $this->iris->put(
            $this->visitors->sessionNamespace($sessionId, 'anonymous_context'),
            ['recent_queries' => array_slice([$query, ...$this->recentQueries($sessionId, $query)], 0, 5)],
            (int) config('memory.anonymous_context_ttl_minutes') * 60,
        );
    }
}
