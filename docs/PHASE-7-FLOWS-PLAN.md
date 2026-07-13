# Phase 7 Plan — Combined Flows

Branch `phase-7-flows`, stacked on `phase-6-nexus`. Implements STRATEGY
§8 flows C, D and E per the Phase 7 roadmap entry (§11) and the task list
in docs/PHASE-6-NOTES.md: trail expansion with persisted, explainable
recommendations; grounded comparison; recommendation transparency; and
the export/deletion guarantees extended to everything new. Success bar:
STRATEGY §12's smallest demo runs end-to-end on the fakes.

## Ground rules carried forward

- **Vendor rule**: contracts + deterministic fakes only. `FakeNexus`
  grows two intents; `live` still fails loudly.
- **Nexus never learns the visitor.** `trailExpansion()` receives a
  neutral `TrailSummary` artifact (artwork ids + exclusions) — no user
  id, no trail id, nothing identifying crosses the boundary. Iris knows
  the visitor; Nexus knows the archive; the orchestration service is the
  only place they meet.
- **Citations over vibes**: every suggestion and every comparison claim
  carries a non-empty `CitationBundle` (enforced at construction since
  Phase 6).
- **Explicit actions only.** The personalization inputs this phase adds
  are all in the charter's enumerated list: added to trail (accept),
  dismissed suggestion, comparison requested. Free-text queries are NOT
  on that list, so signed-in explore still stores nothing (see explore
  seeds below).
- Form-based UI in starter conventions, as in Phases 4–6 (no Livewire —
  the Phase 4 deviation stands).

## 1. Nexus contract growth

Artifacts (`app/Services/Nexus/Artifacts`):

- `TrailSummary` — retrieval input: `artworkIds` (the trail, ordered) +
  `excludeArtworkIds` (already-suggested and dismissed works). Neutral by
  construction.
- `TrailExpansion` — `continuations: list<RelatedArtwork>` and
  `counterpoints: list<RelatedArtwork>` (charter: broaden, don't narrow),
  plus `isEmpty()`/`artworkIds()`.
- `ComparisonClaim` — one grounded statement (`similarity`/`difference`
  implied by which list it sits in) + `CitationBundle`.
- `ArtworkComparison` — `artworkIds`, `similarities`, `differences`.
- `RelatedArtwork` gains `KIND_CONTINUATION` / `KIND_COUNTERPOINT`.

Contract: `NexusRetriever::trailExpansion(TrailSummary, ?int $limit)` and
`NexusRetriever::compare(array $artworks)` (2–4 `Artwork`s). `MotifTrace`
and `ArtworkContext` stay deferred — no surface consumes them this phase.

`FakeNexus`:

- `trailExpansion`: derive anchors from the summary's works (artists,
  classifications, mediums, departments, period range); continuations
  score by anchor overlap (same weights as `relatedTo`); counterpoints
  share the trail's dominant classification (a readable bridge) but
  differ in artist and in medium or period (>50 years) — reasons say so
  plainly, citations quote the actual fields. Both exclude trail +
  excluded ids; deterministic ordering; limit split ≈ 2/3 continuations,
  1/3 counterpoints.
- `compare`: per-dimension claims across the selected works — artists,
  classification, medium, department, period. Shared value → similarity
  claim citing each work's field; differing → difference claim citing
  each value. No metadata overlap at all → empty lists (the view says the
  record holds too little; nothing is invented).

## 2. Memory layer

- **`recommendations` table**: user FK, research_trail FK (cascade),
  artwork FK, `kind`, `reason`, `citations` jsonb, `context` jsonb (the
  snapshot behind "why this suggestion?" — trail title/size, anchors,
  dismissals respected), `status` shown|accepted|dismissed (default
  shown), `responded_at`, timestamps. Every shown suggestion is a row —
  flow E answers from what the system *actually did*, no fresh AI call.
- `Recommendation` model + factory; `User::recommendations()`.
- `MemoryEvent` types: `suggestion_dismissed`, `comparison_requested`
  (both in the charter's enumerated list). Accepting writes the existing
  `artwork_saved_to_trail` event via `TrailService::addArtwork` with
  `origin=suggestion_accepted` — one act, one ledger row.
- **`trail:{id}:semantic_summary`** (deferred since Phase 5):
  `TrailMemoryService::semanticSummary()` — content-hash keyed (ordered
  artwork ids), rebuilt when the hash mismatches, written only under
  `canRemember`, human-readable payload (anchors by name) so the
  dashboard shows something legible. `VisitorContextService` gains
  `trailPrefix()`/`trailNamespace()`. Trail delete/archive forgets the
  trail's prefix; pause purges all the user's trail prefixes (no basis
  for holding derived context while paused; rebuilds on use).
- **`user:{id}:recommendation_context`** (deferred since Phase 5):
  `RecommendationContextService` — dismissed artwork ids, Iris-cached
  with a short TTL (`memory.recommendation_context_ttl_minutes`, new,
  default 240), rebuilt from SQL on miss, read straight from SQL when
  memory may not be written. The ONLY component that assembles
  personalization input; dismissal suppression is user-wide.
- Env validation + `.env.example` for the new var (safe default).

## 3. Flow C — Expand this trail

`TrailExpansionService` (`app/Services/Trails`, one method per step):

- `expand(trail)`: guards (active trail, `trails.expansion_min_items`),
  builds `TrailSummary` (items + exclusions: trail items, anything
  already recommended for this trail in any status, user-wide
  dismissals), calls the retriever with
  `trails.suggestions_per_expansion`, persists each suggestion as a
  `Recommendation(shown)` row in one transaction. The rows are the
  record of the request — no separate ledger event (requesting isn't in
  the enumerated personalization inputs; showing is not remembering
  *about* the user).
- `accept(recommendation)`: `TrailService::addArtwork(origin=
  suggestion_accepted)` + row → accepted. Ledger row comes from
  addArtwork.
- `dismiss(recommendation)`: row → dismissed + `suggestion_dismissed`
  event, then refresh recommendation context — suppression is immediate.
- `pending(trail)`: open (shown) rows, rendered persistently on the
  trail page until answered — transparency survives the redirect and
  the session.

Routes (consented group): `POST /account/trails/{trail}/expand`,
`POST /account/suggestions/{recommendation}/accept` and `/dismiss`
(ownership resolved through the user, never trusted). Pause blocks all
three via the existing middleware (unsafe methods).

UI on the trail page: quiet "Expand this trail" affordance once the
trail has enough items; suggestion cards split into "Continuations" and
"Counterpoints", each with reason, "Why this suggestion?" (stored
citations + the stored context snapshot), Add-to-trail and Dismiss.
Accepted items show a quiet "added from a suggestion" origin note —
AI influence stays inspectable.

## 4. Flow D — Compare

`GET /archive/compare?works[]=…` — `private` + `auth` + `consented`
(STRATEGY route table). Works must be the user's own saved or trailed
artworks (2–4), resolved never trusted. `comparison_requested` ledger
event with a dedupe window (reuses `trails.revisit_dedupe_minutes` — a
refresh is not a second request) and skipped entirely while paused
(pause stops writes, never reads). View: side-by-side works, grounded
similarities and differences, per-claim "Grounded in" citations, and a
"Save to this trail" affordance when arriving from a trail (closes the
loop to flow A). Entry point: checkbox selection on the trail page.
Iris selection context from STRATEGY's sketch is skipped — the query
string carries the selection; nothing worth holding (deviation).

## 5. Flow E — transparency + seeded explore

- "Why this suggestion?" renders the stored `Recommendation` row —
  reason, citations, context-at-the-time. Nothing is recomputed.
- Explore, signed-in + current consent: a "From your trails" line of
  seed query links derived from trail anchors (artist/medium/
  classification names — all products of enumerated explicit saves).
  Results for any query remain identical for every visitor; what is
  personal is only the *starting points*, each visibly derived. No
  Iris writes on explore for signed-in users (the Phase 6 test keeps
  passing); free-text queries are not an enumerated input and are not
  stored durably (deviation from STRATEGY's "auth: user context" route
  sketch, in the consent model's favor).

## 6. Export, deletion, dashboard

- Export: `suggestions` section (kind, reason, status, citations,
  context, timestamps) + held context now includes the user's
  `trail:{id}:` namespaces.
- Deletion: counts + deletes recommendations; purges `user:{id}:` AND
  every `trail:{id}:` prefix with read-back verification each.
  `memory_deleted` receipt gains a suggestions count.
- Dashboard: held-context panel includes trail summaries; a quiet
  suggestions line (how many shown, where they're explained).

## Tests

1. `Nexus/TrailExpansionTest` — determinism, exclusion of trail/excluded
   ids, counterpoints differ and cite, kinds, limits, compare claims.
2. `Trails/TrailExpansionFlowTest` — expand persists shown rows +
   renders with "Why this suggestion?"; min-items gate; accept → item
   origin + row status + ledger; dismiss → row + event + context; a
   re-expansion never re-suggests a dismissed work; paused 403;
   non-owner 404.
3. `Memory/RecommendationContextTest` — SQL rebuild, Iris cache + TTL,
   nothing written while paused, purge on pause; semantic summary
   content-hash rebuild + forget-on-delete.
4. `Archive/CompareTest` — claims cited both ways, event + dedupe,
   guest redirected, foreign works rejected, paused reads fine/writes
   nothing.
5. Export/deletion additions; explore seeds (consented sees, guest
   doesn't, still zero Iris writes).

Full suite + phpstan green before notes; one new migration.
