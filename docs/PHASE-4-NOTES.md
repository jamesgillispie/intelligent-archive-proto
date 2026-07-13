# Phase 4 Notes — Research Trails and Explicit Memory

Built 2026-07-13 on branch `phase-4-trails` (stacked on `phase-3-auth-consent`).
All tests green (109, up from 73), PHPStan clean. Implements
docs/PHASE-4-TRAILS-PLAN.md; flows A ("save artwork to trail") and B
("continue where I left off", SQL-only) work end-to-end with zero AI.

## What was built

1. **The SQL memory layer** — `research_trails`, `trail_items` (unique per
   trail+artwork, `origin` field from day one), `artwork_saves`, `user_notes`
   (morphable), and the append-only `memory_events` ledger
   (`$timestamps = false`, jsonb payload snapshots so rows stay readable after
   their subjects are deleted). Models in `app/Models/Memory`, factories under
   `database/factories/Memory`.
2. **Action services** — `Trails\TrailService`, `Memory\{MemoryEventService,
   ArtworkSaveService, NoteService}`: every explicit action is one transaction
   writing the SQL rows and the ledger event together. `ConsentService` gained
   `pause()`/`resume()`/`renew()`; the switches are ledgered too.
3. **Surfaces** — `/account/trails` (list, start-a-trail, continue panel),
   `/account/trails/{trail}` (workspace: numbered timeline, item notes,
   trail notes, archive/delete), `/account/save/{slug}` (the picker: save for
   later / add to a trail / start a new trail), `/account/memory` (SQL-only
   dashboard: agreement state, pause switch, saves, ledger with per-row
   deletion), and consent renewal on `/account/consent`.
4. **The save affordance** on artwork pages is a static link — identical for
   every visitor, so the publicly cached page stays cache-safe; all per-user
   state lives on the uncached picker behind it. Guests who follow it land on
   login and return via the intended-URL redirect: the consent boundary made
   visible.

## Findings and gotchas

- **Per-user UI cannot exist on the public archive pages.** They are
  stateless and publicly cached (Phase 3 finding), so `auth()` is always null
  there. The pattern that works: static affordance on the cached page →
  uncached `private` page for everything per-user.
- **`consented` blocks all unsafe methods while paused — so anything that
  must work while paused or stale lives in the auth-only tier** of the
  account group: the dashboard (inspect always), every DELETE (delete
  always, charter #8), pause/resume, and consent renewal. Ownership there is
  enforced by resolving through `$request->user()` relationships (strangers
  get 404s); tests lock each of these rights in.
- **Revisit dedupe rides on `last_activity_at`** rather than a query against
  previous revisit events: any activity within `trails.revisit_dedupe_minutes`
  (default 60) makes a return "the same visit", which also stops
  freshly-created trails from logging a same-minute "returned".
- **The ledger never writes about itself** — deleting a ledger row is not
  ledgered, deleting a trail leaves a receipt (`trail_deleted` with title and
  item count in the payload, subject `null` because the subject is gone).

## Deviations, and why

1. **Livewire was not added** (STRATEGY §5 suggested `livewire/livewire` ^3
   this phase; PHASE-3-NOTES said verify first). Verified against the caching
   architecture: the components it was slated for (save buttons on cards and
   detail pages) sit on stateless publicly-cached pages where per-user
   rendering is impossible without de-caching the archive or poisoning the
   shared cache. The picker-page pattern needs only server-rendered forms.
   Revisit when a genuinely reactive private surface arrives (semantic
   explore, compare — Phases 6–7).
2. **`TrailItem`, not `ResearchTrailItem`** — shorter name, same object as
   STRATEGY §3; table `trail_items`.
3. **Consent renewal shipped now, not in Phase 5.** The consent page had
   promised renewal "with the memory dashboard", and the dashboard's SQL-only
   version is Phase 4 scope — shipping the page without the renewal button
   would have kept stale-consent users dead-ended. `ConsentService::grant`
   became `updateOrCreate` so re-granting a revoked version reopens its row
   instead of tripping the unique constraint.
4. **The trail workspace lives at `/account/trails/{trail}`**, not STRATEGY
   §5's `/archive/trails/{trail}` — `/archive/*` is the public cached space,
   and the owner workspace is private. The archive path stays reserved for
   published FeaturedTrails (Phase 8).
5. **Item reordering deferred** — trails append in save order; the reorder
   behavior can arrive with the JS pass.

## Exact commands

```sh
# Verification:
vendor/bin/phpunit                  # 109 tests, 438 assertions
composer phpstan                    # clean
php artisan route:list | grep account
```

No new env vars required; `TRAILS_REVISIT_DEDUPE_MINUTES` exists with a safe
default in `config/trails.php`.

## Phase 5 task list (Redis Iris memory layer, per STRATEGY §7/§11)

1. `IrisClient` contract + `FakeIris` behind `app/Services/RedisIris`
   (contract-only; no real client until official docs are in session).
2. Namespaces: `user:{id}:active_trail` first (powers "Continue" via Iris with
   SQL verification), then `memory_flags`, `trail:{id}:semantic_summary`.
3. `VisitorContextService`, `TrailMemoryService`, `MemoryExportService`,
   `MemoryDeletionService` — full export (JSON + human-readable) and
   whole-memory deletion with purge + read-back verification, from the
   dashboard.
4. Consent revocation flow (revoke → deletion path), completing the
   grant/renew/pause/revoke lifecycle.
5. Carry-overs still open from Phase 2: `missing_from_source_at` sweep,
   `nga:fetch` snapshot command.
