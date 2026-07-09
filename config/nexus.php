<?php

/*
 * Pinecone Nexus — archive knowledge layer.
 *
 * Vendor API rule (CLAUDE.md): no concrete API calls or SDK shapes until
 * official docs are provided. The 'fake' driver is the default everywhere;
 * 'live' additionally requires the 1Password-only keys below.
 */
return [
    // 'fake' (deterministic, in-process) or 'live' (real vendor client, not yet implemented).
    'driver' => env('NEXUS_DRIVER', 'fake'),

    // Logical index name for archive documents.
    'index_name' => env('NEXUS_INDEX_NAME', 'archive-local'),

    // Default number of results for retrieval queries.
    'retrieval_limit' => (int) env('NEXUS_RETRIEVAL_LIMIT', 8),

    'timeout' => (int) env('NEXUS_TIMEOUT', 10),

    // 1Password-only. Required (presence-validated) only when driver=live.
    'api_key' => env('NEXUS_API_KEY'),
    'endpoint' => env('NEXUS_ENDPOINT'),
];
