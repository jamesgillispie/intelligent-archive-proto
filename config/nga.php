<?php

/*
 * National Gallery of Art Open Data ingestion.
 * All values are placeholder-safe; nothing in this file is a secret.
 */
return [
    // Directory containing the NGA Open Data CSV files (objects.csv, constituents.csv, ...).
    'open_data_path' => env('NGA_OPEN_DATA_PATH', storage_path('app/nga')),

    'import' => [
        // Maximum number of artworks to import per run (curated subset guardrail).
        'limit' => (int) env('NGA_IMPORT_LIMIT', 500),

        // Curated subset filter: only objects with this classification are promoted.
        'classification' => env('NGA_IMPORT_CLASSIFICATION', 'Painting'),

        // Only accessioned, non-virtual objects are eligible.
        'accessioned_only' => (bool) env('NGA_IMPORT_ACCESSIONED_ONLY', true),

        // Require a primary published image for an object to enter the subset.
        'require_primary_image' => (bool) env('NGA_IMPORT_REQUIRE_PRIMARY_IMAGE', true),
    ],

    'iiif' => [
        // Phase 2: image validation over the NGA IIIF endpoints.
        'timeout' => (int) env('NGA_IIIF_TIMEOUT', 10),
        'rate_limit_per_minute' => (int) env('NGA_IIIF_RATE_LIMIT_PER_MIN', 60),
    ],
];
