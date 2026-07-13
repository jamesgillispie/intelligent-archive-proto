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

    'fetch' => [
        // Upstream dataset repository (public, CC0) and branch. nga:fetch
        // pins a snapshot to one commit SHA so every import is repeatable.
        'repo' => env('NGA_FETCH_REPO', 'NationalGalleryOfArt/opendata'),
        'branch' => env('NGA_FETCH_BRANCH', 'main'),

        // Raw-file and API hosts — overridable for tests and mirrors.
        'raw_base_url' => env('NGA_FETCH_RAW_BASE_URL', 'https://raw.githubusercontent.com'),
        'api_base_url' => env('NGA_FETCH_API_BASE_URL', 'https://api.github.com'),

        // Directory inside the repository holding the CSV files.
        'data_dir' => env('NGA_FETCH_DATA_DIR', 'data'),

        // Where pinned snapshots land: {snapshots_path}/{sha}/*.csv
        'snapshots_path' => env('NGA_FETCH_SNAPSHOTS_PATH', storage_path('app/nga/snapshots')),

        // Per-file download timeout in seconds (objects.csv is large).
        'timeout' => (int) env('NGA_FETCH_TIMEOUT', 600),
    ],

    'iiif' => [
        // Image download/validation over the NGA IIIF endpoints.
        'timeout' => (int) env('NGA_IIIF_TIMEOUT', 15),
        'rate_limit_per_minute' => (int) env('NGA_IIIF_RATE_LIMIT_PER_MIN', 120),
    ],

    'images' => [
        // Bounding box (pixels) for downloaded primary images. Other sizes
        // remain derivable from the stored IIIF base URL at any time.
        'download_size' => (int) env('NGA_IMAGE_DOWNLOAD_SIZE', 1600),
    ],
];
