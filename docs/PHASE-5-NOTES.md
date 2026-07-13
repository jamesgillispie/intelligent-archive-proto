# Phase 5 Notes — Redis Iris Memory Layer

Built 2026-07-13 on branch `phase-5-iris` (stacked on `phase-4-trails`).
All tests green (133, up from 109), PHPStan clean. Implements
docs/PHASE-5-IRIS-PLAN.md: the consented contextual layer over the
existing Iris contract, full export, whole-memory deletion with purge
verification, consent revocation — and both Phase 2 carry-overs. Still
zero AI. Flow B now runs through Iris; flow F lands end-to-end.

## What was built

1. **The active-trail pointer** — `TrailMemoryService` maintains
   `user:{id}:active_trail` after every explicit trail action (start,
   save-to-trail, item removal, trail note, deliberate revisit,
   reactivate); archiving or deleting the pointed-at trail drops it.
   "Continue where you left off" reads the pointer, verifies it against
   SQL, and rebuilds from the most recent active trail on any miss — a
   test proves the panel follows the pointer over SQL recency.
   `VisitorContextService` is the one mapping from visitor to namespace
   prefix (`user:{id}:` / `session:{id}:`, isolation tested).
2. **Consent gates every Iris write** — `ConsentService::canRemember()`
   (current consent + not paused) checked at write time. Pause purges the
   user's whole Iris prefix; resume rebuilds lazily on next use.
3. **Export** — `MemoryExportService` produces one portable document:
   agreements, switches, trails + works + notes, saves, the full ledger,
   and the held Iris context verbatim. `/account/export` shows it,
   `/account/export/download` attaches it as JSON.
4. **Whole-memory deletion (flow F)** — `MemoryDeletionService`: one SQL
   transaction, a minimal counts-only receipt (`memory_deleted`, itself
   deletable), then an Iris purge with **read-back verification** — a
   purge that cannot prove itself throws. Confirmation page states
   exactly what disappears, with counts.
5. **Revocation** — `ConsentService::revoke()` deletes everything first,
   then stamps `revoked_at`; the ledger keeps two receipts (deletion,
   then revocation). Re-consenting reopens the same version row and
   starts from nothing. The grant/renew/pause/revoke lifecycle is now
   complete and fully tested.
6. **Dashboard** — new "Held context" section rendering
   `inspect("user:{id}:")` verbatim, plus the quiet exit row: export,
   delete everything, revoke the agreement.
7. **Phase 2 carry-overs** — the `missing_from_source_at` sweep (flags
   source rows absent from the snapshot files, clears them on
   reappearance, never deletes, reported per table) and `nga:fetch`
   (four CSVs pinned to an upstream commit SHA under
   `storage/app/nga/snapshots/{sha}/` with a `snapshot.json` manifest the
   import report picks up).

## Findings and gotchas

- **Iris writes happen after the SQL transaction commits.** A write
  inside the transaction could survive a rollback; a write after it can
  at worst leave a stale pointer — harmless, because every read is
  SQL-verified before anything renders.
- **The pointer only reflects ledgered activity.** A revisit inside the
  dedupe window writes neither a ledger row nor a pointer refresh, which
  keeps every Iris namespace strictly derivable from SQL (the
  rebuildability guarantee is real, not aspirational).
- **`Http::fake` + streamed downloads:** faked response bodies arrive
  with their stream pointer at the end. `nga:fetch` rewinds seekable
  bodies before writing to disk; real streamed bodies are not seekable
  and are unaffected.
- **The sweep's definition of "missing" is absence from the files**, not
  absence from the curated subset — full-file scans mark presence for
  every known source id even when the subset filter leaves the row
  unselected (test-pinned), so shrinking the import limit can never
  flag records.

## Deviations, and why

1. **The Iris fake is cache-backed now**, not an in-process array
   (STRATEGY §7 allowed "array/Redis-hash-backed"). The array store dies
   with each request, which made the dashboard's held-context panel
   permanently empty in the browser. Riding the application cache keeps
   tests hermetic (`CACHE_STORE=array`) and makes held context real in
   local dev. Still zero vendor anything; `live` still throws.
2. **`RecommendationContextService`, `user:{id}:memory_flags` and
   `trail:{id}:semantic_summary` are deferred.** Their only consumers are
   the Phase 6/7 explore-and-suggest surfaces; building them now would be
   fake data nobody reads. `memory_flags` in particular is a request-time
   cache of two already-indexed SQL booleans — premature at prototype
   scale.
3. **`canRemember()` lives on `ConsentService`, not
   `VisitorContextService`.** The strategy sketched VisitorContext as
   "who is this and what may we remember"; splitting permission (consent)
   from addressing (prefixes) avoids a circular constructor dependency —
   ConsentService needs prefixes to purge on pause/revoke.
4. **The export page's human-readable form is the document itself** —
   counts, a download button, and the pretty-printed JSON verbatim —
   rather than a second bespoke layout. The browsable rendering of the
   same information *is* the dashboard, one link away; the export page's
   job is transparency about exactly what leaves in the file.
5. **Snapshots land under `storage/app/nga/snapshots/{sha}`** rather than
   STRATEGY §2's `storage/app/nga/{sha}` — the parent directory is the
   long-standing `NGA_OPEN_DATA_PATH` default and already holds loose
   CSVs; nesting keeps both layouts working. The SHA is recorded in the
   import report (via the manifest) rather than a new column.

## Exact commands

```sh
# Verification:
vendor/bin/phpunit                  # 133 tests, 582 assertions
composer phpstan                    # clean

# Pin + import a fresh snapshot (public dataset, no secrets):
op run --env-file=.env.1password -- php artisan nga:fetch
op run --env-file=.env.1password -- php artisan nga:import --path=storage/app/nga/snapshots/{sha}
```

No new migrations. New env vars are all optional with safe defaults
(`NGA_FETCH_REPO`, `NGA_FETCH_BRANCH`, `NGA_FETCH_RAW_BASE_URL`,
`NGA_FETCH_API_BASE_URL`, `NGA_FETCH_DATA_DIR`, `NGA_FETCH_SNAPSHOTS_PATH`,
`NGA_FETCH_TIMEOUT`); no secrets anywhere in this phase.

## Phase 6 task list (Nexus archive layer, per STRATEGY §6/§11)

1. Grow the Nexus boundary from the Phase 1 scaffold
   (`NexusArchiveContract`/`FakeNexusArchive` with `relatedTo()`) into the
   strategy's contracts: `NexusIndexer` + `NexusRetriever`, typed DTOs
   (`ArtworkContext`, `RelatedArtworkSet`, `TrailExpansion`,
   `CitationBundle`…) — citations required on every interpretive DTO,
   fakes only (vendor rule).
2. Artwork-document building from canonical data only: approved
   enrichments and editorial text, never raw source, never AI-pending.
3. Queued indexing jobs (`app/Jobs/Indexing`) subscribed to promotion —
   verify what promotion event exists from Phase 2 before wiring.
4. Related-works panel on artwork detail (non-personalized, cited,
   cache-safe on the public page) and `/archive/explore` for anonymous
   visitors — rate-limited via `memory.anonymous_explore_rate_limit`,
   session context under `session:{id}:` (the prefix isolation is already
   tested).
5. First personalized surface can then use `TrailMemoryService` +
   `trail:{id}:semantic_summary` (deferred from this phase) in Phase 7.
