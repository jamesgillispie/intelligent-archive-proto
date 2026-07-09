<?php

/*
 * Redis Iris — consent-based visitor memory (contextual layer only; SQL is
 * always the durable record).
 *
 * Vendor API rule (CLAUDE.md): no concrete API calls or SDK shapes until
 * official docs are provided. The 'fake' driver is the default everywhere;
 * 'live' additionally requires the 1Password-only keys below.
 */
return [
    // 'fake' (in-process store) or 'live' (real vendor client, not yet implemented).
    'driver' => env('REDIS_IRIS_DRIVER', 'fake'),

    // Prefix for every Iris key namespace (user:{id}:..., session:{id}:...).
    'key_prefix' => env('REDIS_IRIS_KEY_PREFIX', 'ia'),

    // Default TTL for consented user context entries, in seconds (14 days).
    'default_ttl' => (int) env('REDIS_IRIS_DEFAULT_TTL', 1209600),

    // TTL for anonymous session context, in seconds. Deliberately short:
    // anonymous context must never accumulate into a profile.
    'anonymous_session_ttl' => (int) env('REDIS_IRIS_ANON_SESSION_TTL', 3600),

    // 1Password-only. Required (presence-validated) only when driver=live.
    'url' => env('REDIS_IRIS_URL'),
    'api_key' => env('REDIS_IRIS_API_KEY'),
];
