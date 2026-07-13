<?php

/*
 * Research trails — the core product object (trails, not feeds).
 * Guardrails only; no secrets here.
 */
return [
    'max_per_user' => (int) env('TRAILS_MAX_PER_USER', 50),

    'max_items' => (int) env('TRAILS_MAX_ITEMS', 200),

    // Minimum items a trail needs before "Expand this trail" is offered.
    'expansion_min_items' => (int) env('TRAILS_EXPANSION_MIN_ITEMS', 3),

    // Suggestions returned per expansion request.
    'suggestions_per_expansion' => (int) env('TRAILS_SUGGESTIONS_PER_EXPANSION', 6),

    // Reopening a trail within this window is one visit, not several; the
    // ledger records deliberate returns, not page refreshes.
    'revisit_dedupe_minutes' => (int) env('TRAILS_REVISIT_DEDUPE_MINUTES', 60),
];
