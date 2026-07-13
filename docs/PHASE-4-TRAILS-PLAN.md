# Phase 4 Plan — Research Trails and Explicit Memory

Trails, saves, notes, and the append-only `MemoryEvent` ledger — the SQL memory
layer that flows A ("save artwork to trail") and B ("continue where I left
off", SQL-only) run on. Zero AI, per the roadmap: STRATEGY.md §11 Phase 4,
refined by the Phase 3 findings in docs/PHASE-3-NOTES.md.

## Goals

1. A consented user can start a named trail, save artworks into it (with an
   optional note), revisit it, archive it, and delete it.
2. Saves outside any trail (`ArtworkSave`) — the lightest explicit signal.
3. Freeform notes on trails (`UserNote`, morphable so artwork notes cost
   nothing later).
4. Every explicit action lands in the `memory_events` ledger — the only
   personalization input stream, fully user-visible.
5. `/account/memory` — the SQL-only memory dashboard: consent state,
   pause/resume, saves, and the ledger, with per-item deletion.
6. Consent renewal on `/account/consent` (the page has promised it since
   Phase 3, and `ConsentService::grant` already supports it).

## The Livewire decision (STRATEGY §5, PHASE-3-NOTES task 2)

**Deferred — not added in Phase 4.** The strategy recommended `livewire/livewire`
^3 for `SaveToTrailButton`/`TrailPicker` on artwork cards and detail pages,
flagged "verify against the Alpine-first starter before committing". Verified;
it does not fit those surfaces:

- Archive pages are **stateless and publicly cached** (`cache:public;…;s_maxage=3600`,
  no session middleware). The server cannot know who is looking at them, so a
  per-user Livewire component there is impossible without either de-caching
  the public archive or leaking one user's state into the shared cache — the
  exact failure mode Phase 3 was built to avoid.
- Every surface Livewire was slated for either lives on a cached page (save
  buttons) or needs nothing reactive in this phase (workspace, dashboard —
  plain forms carry them fully).

Instead the save affordance is a **static "Save to trail" link** on the artwork
page (identical for every visitor, so cache-safe) leading to an uncached,
consent-gated picker page at `/account/save/{slug}`. Guests hitting it land on
login — the consent boundary made visible — and return via the intended-URL
redirect. Server-rendered forms, no new packages; slowness as a feature.
Livewire can still arrive in a later phase for genuinely reactive private
surfaces (semantic explore, compare).

## Data model (`app/Models/Memory`, per STRATEGY §3)

| Table | Columns | Notes |
|---|---|---|
| `research_trails` | user FK, `title`, `intent_note?`, `status` (active/archived), `last_activity_at` | ordered by activity for "Continue" |
| `trail_items` | trail FK, artwork FK, `position`, `origin` (user_saved/suggestion_accepted), `note?` | unique (trail, artwork); origin keeps AI influence inspectable from day one |
| `artwork_saves` | user FK, artwork FK | unique (user, artwork) |
| `user_notes` | user FK, `notable` morph, `body` | Phase 4 UI writes trail notes only |
| `memory_events` | user FK, `event_type`, `subject` morph?, `payload` jsonb?, `occurred_at` | append-only (`$timestamps = false`); payload snapshots titles so the ledger stays readable after subjects are deleted |

Model name: `TrailItem` (per PHASE-3-NOTES) rather than STRATEGY's
`ResearchTrailItem` — same object.

## Services

- `Memory\MemoryEventService::record(user, type, subject?, payload?)` — the
  one way ledger rows are written.
- `Trails\TrailService` — start / addArtwork / removeItem / archive /
  reactivate / delete / recordRevisit. Every mutation is a transaction that
  writes the SQL rows **and** the event together. Limits from `config/trails.php`
  (`max_per_user`, `max_items`); revisits deduped within
  `revisit_dedupe_minutes` (new key) and skipped while paused.
- `Memory\ArtworkSaveService`, `Memory\NoteService` — same
  transaction-plus-event shape.
- `ConsentService` gains `pause()` / `resume()` / `renew()` — pause state
  already lives solely on `personalization_settings` (Phase 3 deviation 1);
  switches are ledgered too.

## Routes (all inside the existing `['private', 'auth']` account group)

Consent-gated writes and working surfaces (`consented` middleware):
trails index/store/show/archive/reactivate, item store (flat
`POST /account/trails/items`, trail chosen in the form), trail note store,
save picker `GET /account/save/{slug}`, `POST /account/saves`.

**Auth-only — "the user can always inspect and delete" (charter #8):**
`GET /account/memory`, pause/resume, consent renewal `POST /account/consent`,
and every `DELETE` (trail, item, save, note, ledger event). Stale consent or
paused memory must never lock a user out of seeing or removing their own
memory, so these sit outside the `consented` gate; ownership is enforced by
resolving everything through the authenticated user's relationships (404 for
anyone else).

## Views

- `site/account/trails.blade.php` — real list: "Continue this trail" panel
  (most recent activity), start-a-trail form, active + archived lists.
- `site/account/trail.blade.php` — workspace: numbered item timeline with
  images, per-item notes, remove; trail notes; archive/delete affordances.
- `site/account/save.blade.php` — the picker: save for later / add to an
  existing trail / start a new trail with this work.
- `site/account/memory.blade.php` — dashboard (SQL-only).
- `site/archive/artwork.blade.php` — quiet static "Save to trail" link.
- `site/account/consent-required.blade.php` — gains the renewal form.
- Stub copy in trails view and the Phase-3 "renewal ships later" promises
  replaced as the surfaces arrive.

## Tests

Trail CRUD + owner-only authorization; ledger writes for every explicit
action; save flows end-to-end (picker → item/save, idempotent duplicates,
limits); revisit recording (deduped, skipped while paused); dashboard
rendering + per-item deletion; pause/resume; consent renewal; deletion rights
under paused and stale consent; account pages never publicly cacheable.

## Out of scope (later phases)

Redis Iris `active_trail` pointer (Phase 5), export + whole-memory deletion
with purge verification (Phase 5), recommendations/expansion (Phases 6–7),
FeaturedTrails public pages (Phase 8), item reordering, Phase 2 carry-overs
(`missing_from_source_at` sweep, `nga:fetch`) — tracked separately.
