# Phase 5 Plan — Redis Iris Memory Layer

Branch `phase-5-iris` (stacked on `phase-4-trails`). Implements STRATEGY §7
and the Phase 5 roadmap entry: the consented contextual layer over the
existing `RedisIrisMemoryContract`, full memory export, whole-memory
deletion with purge verification, consent revocation — plus the two Phase 2
carry-overs. Still zero AI; flow B moves onto Iris, flow F lands
end-to-end.

## Ground rules carried in

- **Vendor rule**: the Iris contract + fake already exist
  (`app/Services/RedisIris`). No real client, no vendor API shapes; the
  `live` driver keeps failing loudly.
- **Iris is never the record**: every namespace is derived from SQL,
  rebuildable from SQL, inspectable on the dashboard, purgeable in one
  operation. SQL deletion + Iris purge + read-back verification = deletion.
- **Consent gates every Iris write**: current consent and unpaused memory,
  checked at write time. Pause purges held context (STRATEGY §7 table);
  resume rebuilds lazily on next use.

## What gets built

### 1. The fake grows cross-request persistence

`FakeRedisIrisMemory` currently holds an in-process array, which dies with
each request — fine for tests, invisible in the browser. It becomes
cache-backed (Laravel `Cache`, still no vendor anything) with the same TTL
semantics and a namespace index supporting `inspect`/`purge` by prefix. In
tests `CACHE_STORE=array` keeps behavior identical; in local dev the
file/database cache lets held context actually survive between requests so
the dashboard inspection panel is real.

### 2. Context services (`app/Services/Memory`)

- `VisitorContextService` — the one place that maps a visitor to an Iris
  namespace prefix and answers "may we remember right now?"
  (`canRemember()` = current consent + not paused). User prefixes
  `user:{id}:`; anonymous session prefixes `session:{id}:` exist for
  Phase 6's explore surface and must never link to a user.
- `TrailMemoryService` — owns `user:{id}:active_trail`:
  - `refreshActiveTrail(trail)` after every explicit trail action
    (start, save-to-trail, item removal, note, revisit, reactivate) —
    called after the SQL transaction commits, consent-gated, TTL from
    `memory.active_trail_ttl_days`.
  - `forgetActiveTrail(user, trailId)` when the pointed-at trail is
    archived or deleted.
  - `activeTrail(user)` — flow B: read pointer → verify against SQL
    (owner's, active) → on miss/failed verification rebuild from the most
    recent active trail and re-warm the pointer. While paused: SQL only,
    no Iris reads or writes.
- `MemoryExportService` — one structured array: consents, switches,
  trails + items + notes, saves, the full ledger, and a verbatim snapshot
  of currently held Iris context. Rendered two ways: human-readable page,
  JSON download.
- `MemoryDeletionService` — flow F: one SQL transaction deleting notes,
  trails (items cascade), saves and ledger; then writes the minimal
  deletion receipt (`memory_deleted` event with counts — itself deletable);
  then purges `user:{id}:` and **verifies by reading back** — a non-empty
  `inspect()` after purge throws.

### 3. Consent lifecycle completed

`ConsentService` gains `revoke()`: delete everything via
`MemoryDeletionService`, stamp `revoked_at` on every active consent row,
record `consent_revoked`. `pause()` additionally purges the user's Iris
prefix (pause stops writes *and* held context; SQL stays). Re-granting
after revocation already works (`grant()` reopens the revoked row —
Phase 4).

### 4. Surfaces

- `/account/memory` — new "Held context" section rendering
  `inspect("user:{id}:")` verbatim, plus quiet links to export, delete
  everything, and revoke.
- `/account/export` (view) + `/account/export/download` (JSON attachment).
- `/account/memory/delete` (confirm page stating exactly what disappears,
  with counts) + `DELETE /account/memory`.
- `/account/consent/revoke` (confirm) + `DELETE /account/consent`.
- All of these live in the **auth-only tier** — export, inspection and
  deletion are rights that survive pause and stale consent (charter #8).
- `/account/trails` "Continue where you left off" now resolves through
  `TrailMemoryService::activeTrail()` (flow B via Iris, SQL-verified).

### 5. Phase 2 carry-overs

- **`missing_from_source_at` sweep** in `NgaImportService`: before the
  passes, load the source ids we already hold per table; during the full
  CSV scans, note which of them appear anywhere in the snapshot (selected
  or not); afterwards stamp the absent ones (`marked_missing` in the
  report) and clear the flag on reappeared ones. Never deletes; canonical
  demotion stays an editorial decision.
- **`nga:fetch`**: downloads the four CSVs at a pinned upstream commit SHA
  into `storage/app/nga/{sha}/` (resolving the default branch head via the
  GitHub API when no `--sha` is given), streams to disk, skips files
  already present (same SHA = no-op), and writes a `snapshot.json`
  manifest (sha, fetched_at, files). `nga:import` picks the manifest up
  and records the SHA in its report. Config under `nga.fetch`
  (repo, branch, base URLs, timeout) — public dataset, no secrets.

## Deferred, deliberately

- `user:{id}:recommendation_context` and `RecommendationContextService` —
  their only consumer is the Phase 7 suggestion loop; building them now
  means fake data nobody reads.
- `trail:{id}:semantic_summary` — exists to feed Nexus expansion queries
  (Phase 6/7).
- `user:{id}:memory_flags` — a request-time cache of two SQL booleans that
  are already one indexed query each; premature at prototype scale.
- Real Iris client — vendor rule; `live` driver still throws.

## Tests

- Trail memory: pointer written on explicit actions only; nothing written
  while paused or with stale consent; pointer survives and refreshes on
  use; stale pointer (deleted/archived trail) falls back to SQL rebuild;
  purge on pause.
- Export: every memory table represented; ledger complete; Iris snapshot
  included; JSON downloads as an attachment; works while paused and with
  stale consent.
- Deletion: everything gone in one action; receipt survives with counts;
  Iris read-back verification (a failing purge aborts loudly); strangers
  unaffected; works while paused and with stale consent.
- Revocation: memory deleted + consent stamped + working surfaces gated +
  dashboard still reachable; re-consent from the consent-required page
  reopens the same row.
- Anonymous isolation: session-prefixed context never appears under any
  user prefix.
- Sweep: record absent from a later snapshot is stamped; reappearing
  clears the stamp; unselected-but-present records are not stamped.
- `nga:fetch`: downloads to `{sha}` directory, idempotent re-run, manifest
  written, missing-SHA resolution, failure is loud.
