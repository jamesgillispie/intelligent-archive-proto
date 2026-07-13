# Phase 6 Notes — Nexus Archive Layer

Built 2026-07-13 on branch `phase-6-nexus` (stacked on `phase-5-iris`).
All tests green (149, up from 133), PHPStan clean. Implements
docs/PHASE-6-NEXUS-PLAN.md per STRATEGY §6/§11: the archive knowledge
boundary — split contracts, cited artifacts, queued indexing, and the
first two Nexus surfaces. Still zero vendor code and zero live AI: the
deterministic fake carries everything, and it defines the quality bar a
live client must meet.

## What was built

1. **Contracts and artifacts (STRATEGY §6 shapes)** — the Phase 1
   scaffold (`NexusArchiveContract`/`FakeNexusArchive`/`ArchiveMatch`)
   grew into `Contracts/{NexusIndexer,NexusRetriever}`,
   `Artifacts/{Citation, CitationBundle, ArtworkDocument, RelatedArtwork,
   RelatedArtworkSet, ExploreResult}` and `Fake/FakeNexus` (one singleton
   bound to both interfaces; `live` still fails loudly). A
   `CitationBundle` cannot be empty, so no interpretive artifact can
   exist uncited — enforced at construction, pinned by test.
2. **Document building** — `ArtworkDocumentBuilder` is the single
   assembly point: canonical display values (source + editorial
   overrides), vocabulary names, relationship edges, and identified
   passages (editorial description, source inscription, **approved**
   enrichments as `enrichment:{id}`). Never raw `source_records`
   payloads; never AI-pending text — both pinned with marker tests.
3. **ArtworkEnrichment (minimal)** — the STRATEGY §4 review table lands
   now (model + migration + factory, `pending`/`approved`/`rejected`)
   because the Phase 6 test list requires "document building excludes
   unapproved text" and the indexer is §10's third layer of defense.
   Nothing generates enrichments yet; the Twill review block arrives
   with generation.
4. **Queued indexing** — `ArtworkPromoted` is fired by
   `ArtworkCanonicalizer` when promotion completes; a listener queues
   `app/Jobs/Indexing/IndexArtwork`, the only caller of `NexusIndexer`:
   displayable → upsert a fresh document; missing/unpublished/
   undisplayable → remove. Twill editorial saves dispatch the same job
   (`ArtworkRepository::afterSave`), so overrides reindex and
   unpublishing removes without a re-import. Idempotence, removal, both
   dispatch paths, and the import→index chain are all tested end-to-end
   on the sync queue.
5. **Related-works panel (first surface, STRATEGY §14)** —
   `NexusRetriever::relatedTo()` feeds "Related in the collection" on
   artwork detail: every entry carries its reason verbatim plus a "Why
   this?" disclosure quoting the cited canonical fields, and the section
   ends with the transparency line ("…never from your activity").
   Non-personalized by construction — the publicly cached page stays
   valid.
6. **Anonymous explore (`/archive/explore`)** — free-text, grounded,
   identical for every visitor. Rate-limited by a new `archive-explore`
   limiter (`memory.anonymous_explore_rate_limit`/hour, keyed by session
   id, or user id when signed in — never by IP). Guests get a quiet
   "Earlier this session" line held in Iris under
   `session:{id}:anonymous_context` with a hard TTL cap
   (`memory.anonymous_context_ttl_minutes`, default 60). Linked from the
   archive landing page ("Explore by idea").

## Findings and gotchas

- **The `ArtworkPromoted` event did not exist.** STRATEGY §3.8 assumed
  it from Phase 2 ("in Phases 1–5 nothing listens"); verified absent, so
  it lands in this phase at the same seam — end of
  `ArtworkCanonicalizer::canonicalize()` — and every promotion (including
  idempotent re-imports) re-queues an upsert, which is safe by
  definition.
- **Guests key the rate limiter by session id**, which only trips when
  the session cookie persists — true in browsers, not across bare test
  requests. The 429 test signs in for a stable key; the session-cookie
  carry (grab the encrypted cookie off response one, send it back with
  `withUnencryptedCookie`) is proven separately by the "earlier this
  session" test.
- **The TTL cap is provable in tests**: the cache-backed Iris fake
  honors `$this->travel()`, so the anonymous-context test travels past
  the TTL and asserts the session prefix is empty — "must never
  accumulate" as an assertion, not a comment.
- **Twill's `afterSave` receives a `TwillModelContract`** — PHPStan
  rejects `$model->id`; use `$model->getKey()`.

## Deviations, and why

1. **FakeNexus retrieval reads the canonical DB, not its recorded
   upserts.** An in-process document store cannot span the queue worker
   and the web request, and the canonical DB *is* the fake's document
   store — retrieval over it keeps every surface real in the browser.
   Upserts are still recorded and asserted (idempotence, removal,
   document contents); a live client will retrieve over its own index
   through the identical contract seam.
2. **`TrailExpansion`, `ArtworkComparison`, `MotifTrace` and
   `ArtworkContext` DTOs are deferred to Phase 7** with their surfaces
   (flows C/D/E). DTO shapes should be designed against consumers;
   Phase 6's two intents needed `RelatedArtworkSet` and `ExploreResult`
   (the latter not in STRATEGY's list — explore needed a query-shaped
   result and reusing `RelatedArtworkSet` would have faked a subject).
3. **`ArtworkEnrichment` lands before anything generates enrichments** —
   deliberately: the exclusion guarantee has to exist before the first
   document is indexed, not after AI text appears. Generation + the
   Twill review block are explicitly out of scope ("no unreviewed AI
   metadata", first pass).
4. **The related panel replaced "More by {artist}"** rather than sitting
   beside it: shared-artist is the fake's strongest relatedness signal,
   so the old panel is subsumed by one that also explains and cites
   itself. One panel, no duplicate rows.
5. **Signed-in explore writes no context at all** (STRATEGY's route
   table sketches "auth: user context"): user-context explore is the
   Phase 7 personalized surface; writing session context for a signed-in
   visitor would link a session to an account — the exact line the
   anonymous namespace must not cross. Tested.

## Exact commands

```sh
# Verification:
vendor/bin/phpunit                  # 149 tests, 651 assertions
composer phpstan                    # clean

# One new migration (artwork_enrichments):
op run --env-file=.env.1password -- php artisan migrate
```

New env vars are optional with safe defaults
(`MEMORY_ANON_CONTEXT_TTL_MINUTES=60`; `MEMORY_ANON_EXPLORE_RATE_LIMIT`
existed since Phase 3). No secrets anywhere in this phase.

## Phase 7 task list (combined flows, per STRATEGY §8/§11)

1. **Flow C — "Expand this trail" (the demo centerpiece)**: grow
   `NexusRetriever` with `trailExpansion()` returning `TrailExpansion`
   (continuations *and* deliberate counterpoints, each cited); build
   `trail:{id}:semantic_summary` in `TrailMemoryService` and
   `RecommendationContextService` over `user:{id}:recommendation_context`
   (both deferred since Phase 5); persist every shown suggestion as a
   `Recommendation` row (new model + migration) with accept/dismiss
   feedback events; dismissals must suppress re-suggestion.
2. **Flow D — compare saved works**: `compare()` → `ArtworkComparison`
   with per-claim citations; `/archive/compare` consent-gated;
   `comparison_requested` is itself an explicit memory event.
3. **Flow E / personalized explore**: seed explore with the user's
   trails (consented), "why this suggestion?" rendering stored evidence.
4. Recommendation transparency UI: `RecommendationExplanation` +
   reuse `x-archive.cited-match`; extend export/deletion to cover
   `Recommendation` rows and the new Iris namespaces (they purge with the
   existing prefix design already).
5. Success bar: STRATEGY §12's smallest demo runs end-to-end on fakes.
