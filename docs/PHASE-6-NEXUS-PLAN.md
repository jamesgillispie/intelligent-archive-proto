# Phase 6 Plan — Nexus Archive Layer

Branch `phase-6-nexus`, stacked on `phase-5-iris`. Implements STRATEGY §6
and §11 Phase 6: the archive knowledge boundary — contracts, typed
artifacts, deterministic fake, queued indexing, and the first two Nexus
surfaces (related works, anonymous explore). Fakes only, per the vendor
rule; no personalization (that is Phase 7).

## Ground rules

- **Vendor rule**: no Pinecone Nexus API shapes, SDK calls, or endpoint
  assumptions. Contracts + `FakeNexus` only; `live` keeps failing loudly.
- **Canonical only**: documents are built from canonical artwork data —
  display fields (source + editorial overrides), vocabulary names,
  editorial description, and **approved** enrichments. Never raw
  `source_records` payloads, never AI-pending text.
- **Citations over vibes**: every interpretive artifact carries a
  `CitationBundle`; an empty bundle cannot be constructed.
- **Non-personalized**: both Phase 6 surfaces are identical for every
  visitor. The related panel stays cache-safe on the public artwork page;
  explore is sessioned only for rate limiting and the session-scoped
  context line.

## 1. Contracts and artifacts (STRATEGY §6 shapes)

Reshape the Phase 1 scaffold (`NexusArchiveContract` / `FakeNexusArchive`
/ `ArchiveMatch`) into the strategy's layout:

- `app/Services/Nexus/Contracts/NexusIndexer` —
  `upsertArtwork(ArtworkDocument)`, `removeArtwork(int)`. Called only by
  queued jobs in `app/Jobs/Indexing`.
- `app/Services/Nexus/Contracts/NexusRetriever` — intent-shaped:
  `relatedTo(Artwork, ?int): RelatedArtworkSet`,
  `explore(string, ?int): ExploreResult`.
- `app/Services/Nexus/Artifacts/` — `Citation` (artwork id + field/passage
  id + quoted value), `CitationBundle` (non-empty; throws otherwise),
  `ArtworkDocument` (fields + identified passages + relationship edges),
  `RelatedArtwork` (kind + human-readable reason + score + bundle),
  `RelatedArtworkSet`, `ExploreResult`.
- `app/Services/Nexus/Fake/FakeNexus` implements both contracts; bound as
  one singleton to both interfaces. Deterministic: relatedness from
  canonical metadata overlap, explore from canonical keyword matching;
  citations always point at the actual overlapping/matching fields.
- `ArtworkDocumentBuilder` — the single place a document is assembled;
  the enforcement point for "approved text only".

Deferred to Phase 7 (their surfaces arrive there): `TrailExpansion`,
`ArtworkComparison`, `MotifTrace`, `ArtworkContext`.

## 2. ArtworkEnrichment (minimal, structural)

STRATEGY §4 puts AI-generated text in `ArtworkEnrichment` rows
(`pending`/`approved`/`rejected`); §10 makes the indexer the third layer
of defense against unreviewed AI text leaking. Nothing *generates*
enrichments yet, but the Phase 6 test list explicitly requires "document
building excludes unapproved text" — so the model, migration, and factory
land now, and `ArtworkDocumentBuilder` indexes `approved` rows only.
Review UI in Twill comes with generation, later.

## 3. Queued indexing

- `App\Events\ArtworkPromoted` — fired by `ArtworkCanonicalizer` when
  promotion completes (STRATEGY expected this event from Phase 2; it was
  never built — verified — so it lands here at the same seam).
- `App\Listeners\QueueArtworkIndexing` — dispatches the job.
- `App\Jobs\Indexing\IndexArtwork` — loads the artwork; displayable →
  build document + `upsertArtwork`; missing/undisplayable/unpublished →
  `removeArtwork`. Idempotent per artwork.
- Twill editorial saves reindex too: `ArtworkRepository::afterSave`
  dispatches the same job (editorial overrides and unpublish must reach
  the index without waiting for a re-import).

## 4. Related-works panel (first Nexus surface, STRATEGY §14)

`ArtworksController::show` swaps `moreByArtist` for
`NexusRetriever::relatedTo()` — shared-artist is the fake's strongest
signal, so the old panel is subsumed, and now every entry carries a
reason and citations. The view renders a quiet "Related in the
collection" section: artwork cards + reason + a "Why this?" disclosure
listing the cited fields verbatim. Non-personalized, so the public page
cache stays valid.

## 5. Anonymous explore (`/archive/explore`)

- Route in the `private` middleware group (session, no response cache),
  throttled by a new `archive-explore` limiter:
  `memory.anonymous_explore_rate_limit` per hour, keyed by session
  (user id when logged in). Anonymous visitors welcome — this is the
  "limited non-personalized semantic exploration" tier.
- `ExploreController`: free-text `q` → `NexusRetriever::explore()` →
  cards with reasons + citations.
- Session context: guests get `session:{id}:anonymous_context` in Iris —
  the last few queries, TTL-capped via new
  `memory.anonymous_context_ttl_minutes` — rendered back as a quiet
  "Earlier this session" line. Strictly session-scoped, never linked to a
  user account. Logged-in visitors get no context writes at all until the
  consented user-context surface exists (Phase 7).
- Link from the archive landing page.

## 6. Tests (STRATEGY Phase 6 list)

- Document building: canonical fields + editorial description + approved
  enrichment included; pending/rejected enrichment excluded; raw source
  payload values never leak; per-passage ids present.
- Indexing: idempotent upsert; import wires promotion → index (sync
  queue); Twill unpublish → removal; editorial save → reindex.
- Retrieval: determinism (same inputs, same ranked output); citations on
  every match; related panel renders reasons on the artwork page.
- Explore: results with reasons anonymously; 429 past the limit; session
  context written for guests only, isolated from `user:` prefixes,
  TTL-capped; nothing written for logged-in visitors.
- DTO guards: empty `CitationBundle` throws.
- `VendorBoundariesTest` rewritten for the split contracts; `live` still
  fails loudly.
