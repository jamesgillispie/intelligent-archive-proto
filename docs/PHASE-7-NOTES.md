# Phase 7 Notes — Combined Flows

Built 2026-07-13 on branch `phase-7-flows` (stacked on `phase-6-nexus`).
All tests green (173, up from 149), PHPStan clean. Implements
docs/PHASE-7-FLOWS-PLAN.md per STRATEGY §8/§11: flows C, D and E on the
fakes — which means **STRATEGY §12's smallest demo now runs end-to-end**:
a consented visitor saves works into a trail, returns, clicks "Expand
this trail", Iris supplies trail context, Nexus returns cited
continuations and counterpoints, and the interface explains why each
suggestion appeared. Still zero vendor code and zero live AI.

## What was built

1. **Nexus contract growth** — `NexusRetriever::trailExpansion()` and
   `::compare()`, with `TrailSummary` (neutral input: artwork ids +
   exclusions, no user or trail identity ever crosses the boundary),
   `TrailExpansion` (continuations *and* counterpoints), and
   `ArtworkComparison`/`ComparisonClaim` (per-claim `CitationBundle`,
   which still cannot be empty). FakeNexus derives continuations from
   anchor overlap (artists, classification, medium, department, period)
   and counterpoints as deliberate contrast over a readable bridge —
   still the dominant classification, but other hands and a different
   medium or period, with the reason saying so plainly.
2. **Recommendation persistence (flow C's spine)** — every suggestion is
   a `recommendations` row (kind, verbatim reason, citations, context
   snapshot, status shown/accepted/dismissed) written in the same
   transaction that shows it. "Why this suggestion?" renders the stored
   row — the explanation is what the system actually did, never a fresh
   query.
3. **The two deferred Iris namespaces** —
   `trail:{id}:semantic_summary` (content-hash keyed on ordered item
   ids; rebuilt on mismatch; human-readable anchors because the
   dashboard shows the payload verbatim; purged on archive, delete and
   pause) and `user:{id}:recommendation_context` (only dismissed artwork
   ids; short TTL via `memory.recommendation_context_ttl_minutes`;
   rebuilt from SQL; read straight from SQL while paused so suppression
   holds without held context).
4. **Expand this trail** — `TrailExpansionService` is the one seam where
   Iris context meets Nexus knowledge. Guards (active trail,
   `trails.expansion_min_items`), exclusion set (trail items, anything
   ever suggested for the trail, user-wide dismissals), persistence,
   accept (`TrailService::addArtwork` with `origin=suggestion_accepted` —
   the trail page shows "From a suggestion you accepted" forever) and
   dismiss (ledgered `suggestion_dismissed`, context refreshed so
   suppression is immediate). Open suggestions render on the trail page
   until answered.
5. **Compare (`/archive/compare`)** — consent-gated GET over the user's
   own saved/trailed works (2–4, resolved never trusted), claims split
   into shared ground and differences with "Grounded in" citations both
   ways, `comparison_requested` ledgered with a dedupe window (reusing
   `trails.revisit_dedupe_minutes`) and skipped while paused. Entry via
   checkboxes on the trail page (HTML `form` attribute — no nested
   forms); "Save to this trail" on the compare page closes the loop to
   flow A.
6. **Trail-seeded explore + full memory coverage** — consented visitors
   get "From your trails" starting points (artist/medium/classification
   names from their own saved works, frequency-ranked); results for any
   query stay identical for every visitor and explore still writes
   nothing for signed-in users. Export gains the complete `suggestions`
   record and trail-keyed held context; deletion counts and removes
   recommendations and purges `user:{id}:` plus every `trail:{id}:`
   prefix with read-back verification; the deletion receipt and confirm
   page name suggestions; the dashboard explains all of it.

## Findings and gotchas

- **Weak overlap starved the counterpoints.** A candidate sharing only
  the trail's dominant classification scored 1.0 as a "continuation" and
  consumed exactly the works counterpoints draw from. Fixed:
  continuations now require a nameable connection (artist, medium or
  period) — a reason a visitor can actually read.
- **Trail-keyed namespaces don't purge with the user prefix.**
  `trail:{id}:` sits outside `user:{id}:`, so pause, deletion, export
  and the dashboard all enumerate the user's trail ids explicitly — and
  deletion captures those ids *before* the SQL rows go.
- **Larastan can't type `whereHas` closures** through the untyped
  relation chain — the hydrate pattern from Phase 6's ExploreController
  (`Artwork::displayable()->findMany()` + `setRelation`) sidesteps it
  and keeps undisplayable works out of rendering.
- **Nested forms**: the compare checkboxes live inside list items that
  already contain Remove forms; the HTML `form=""` attribute binds them
  to a single GET form declared after the list.

## Deviations, and why

1. **No `trail_expansion_requested` ledger event.** Requesting
   suggestions is not in the charter's enumerated personalization
   inputs, and the Recommendation rows are already the durable,
   inspectable record of what was shown. Dismissals and accepts — the
   enumerated signals — are ledgered.
2. **Accepting writes one ledger row, not two** — the
   `artwork_saved_to_trail` event from `TrailService::addArtwork`, with
   the item's origin carrying the suggestion provenance. A second
   "suggestion accepted" event would double-ledger one act.
3. **Compare skips STRATEGY's short-lived Iris selection context** —
   the query string carries the selection; there is nothing worth
   holding.
4. **`RecommendationFeedback` folded into `Recommendation.status`** —
   "not helpful" is dismiss in the first pass; a separate feedback table
   can arrive with finer-grained signals if ever needed.
5. **Signed-in explore still stores no queries** (STRATEGY's route
   table sketched "auth: user context"): free-text queries are not an
   enumerated explicit action. Personalization of explore is seed links
   derived from trails — visible, explainable, and read-only.
6. **Dismissal suppression is user-wide**, the strong reading of
   "dismissals must suppress re-suggestion" — dismissed once, never
   suggested again in any trail.
7. **`MotifTrace` and `ArtworkContext` stay deferred** — still no
   surface consumes them (motif trace is the feature slate's stretch
   item).

## Exact commands

```sh
# Verification:
vendor/bin/phpunit                  # 173 tests, 787 assertions
composer phpstan                    # clean

# One new migration (recommendations):
op run --env-file=.env.1password -- php artisan migrate
```

New env var is optional with a safe default
(`MEMORY_RECOMMENDATION_CONTEXT_TTL_MINUTES=240`). No secrets anywhere
in this phase.

## Phase 8 task list (polish, eval, docs — per STRATEGY §11)

1. Interface-language pass over every surface against the charter's
   quiet-affordance vocabulary; empty/edge states.
2. Methodology page: complete it with the guarantees now provable —
   no per-user data in Nexus, deletion with read-back verification,
   the enumerated-inputs list, the suggestion record.
3. Curated demo content: seed trails/curated sets over the imported
   subset so the smallest demo shows well; demo script.
4. Performance: response-cache coverage for anonymous archive pages,
   query audit on the expansion/compare paths.
5. Accessibility pass (skip links, alt text via `assistivetext`,
   keyboard flow through suggestion cards and compare selection).
6. `docs/` runbooks: import, queue worker, real-vendor swap notes
   (what a live NexusRetriever/Iris client must satisfy — the fakes
   define the bar; the contract seams are the swap points).
7. Stretch, if time allows: `MotifTrace` + "Trace this motif";
   enrichment generation behind the Twill review block (STRATEGY §4's
   pending→approved workflow now has its table waiting).
