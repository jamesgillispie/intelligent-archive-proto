<?php

/*
 * Visitor memory and consent settings. Login is the consent boundary for
 * durable memory (CLAUDE.md); these values shape how memory behaves once
 * consent exists. No secrets here.
 */
return [
    // Version identifier of the consent text a user agreed to. Bump when the
    // consent wording changes; users on older versions are re-prompted.
    'consent_version' => env('MEMORY_CONSENT_VERSION', '2026-07-01'),

    // How long the "continue where I left off" pointer stays warm, in days.
    'active_trail_ttl_days' => (int) env('MEMORY_ACTIVE_TRAIL_TTL_DAYS', 30),

    // Anonymous visitors get limited, non-personalized semantic exploration:
    // requests per session per hour.
    'anonymous_explore_rate_limit' => (int) env('MEMORY_ANON_EXPLORE_RATE_LIMIT', 20),

    // Default export format for the memory export endpoint (Phase 5).
    'export_format' => env('MEMORY_EXPORT_FORMAT', 'json'),
];
