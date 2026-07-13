# Vendor Swap Notes — replacing the fakes with live clients

Redis Iris and Pinecone Nexus run as deterministic in-process fakes behind
Laravel contracts. This document is the specification a live client must
satisfy. The fakes are not placeholders — they define the behavioral bar;
the application code must not change when the drivers do.

**The vendor API rule still applies (CLAUDE.md):** no concrete API calls,
SDK usage, or request/response shapes may be written from memory. A live
client gets implemented only when official docs or SDK references are in
hand.

## Where the seams are

- Contracts: `app/Services/Nexus/Contracts/{NexusRetriever,NexusIndexer}.php`
  and `app/Services/RedisIris/RedisIrisMemoryContract.php`.
- Binding: `app/Providers/ArchiveServiceProvider.php::register()` — driver
  selection via `config('nexus.driver')` / `config('redis_iris.driver')`
  (`NEXUS_DRIVER`, `REDIS_IRIS_DRIVER`). Any driver other than `fake`
  currently throws; a live client adds a `match` arm, nothing more.
- Config: `config/nexus.php`, `config/redis_iris.php` — credentials arrive
  as new typed keys there, validated by name in
  `EnvironmentValidationService`, populated via 1Password locally and
  platform secrets in production. Secrets never appear in code or logs.

## What a live Nexus client must satisfy

Interface (`NexusRetriever` + `NexusIndexer`):

- `upsertArtwork(ArtworkDocument): void` / `removeArtwork(int): void` —
  called from the queued indexing jobs; unpublishing removes.
- `relatedTo(Artwork, ?int): RelatedArtworkSet`
- `explore(string, ?int): ExploreResult`
- `trailExpansion(TrailSummary, ?int): TrailExpansion`
- `compare(list<Artwork>): ArtworkComparison`

Behavioral bar (all of it test-pinned against the fake):

1. **Visitor-blind, permanently.** The client receives artwork ids, artwork
   models, or a neutral `TrailSummary` (ids + exclusions). It must never be
   handed — and must never request — a user id, session id, trail title, or
   anything traceable to a visitor. Deletion keeps its "nothing to delete
   on the other side" guarantee only if this holds.
2. **Citations or nothing.** `CitationBundle` cannot be empty by
   construction. A result the client cannot ground in fields of the
   collection record must be dropped, not padded. Reasons are visitor-facing
   sentences; continuations require a nameable connection.
3. **Exclusions are hard.** Ids in `TrailSummary::excludeArtworkIds` must
   never come back. Dismissal suppression sits on top of this.
4. **Deterministic enough to explain.** Same archive state + same input →
   same output ordering (the fake ties-breaks by id). If the live service
   is stochastic, pin whatever seed/params make results reproducible — an
   explanation that changes on refresh is not an explanation.
5. **Both kinds, honestly labeled.** `trailExpansion` returns continuations
   (deepen: shared hand, medium, period) and counterpoints (broaden: a
   readable bridge plus deliberate contrast), using
   `RelatedArtwork::KIND_CONTINUATION` / `KIND_COUNTERPOINT`.
6. **Displayability is the app's job, but drop unknowns.** The app filters
   to displayable works on render; the client should still only return ids
   that exist in the index it was given.

## What a live Iris client must satisfy

Interface (`RedisIrisMemoryContract`):

- `put(string $namespace, array $context, ?int $ttlSeconds): void`
- `get(string $namespace): ?array`
- `forget(string $namespace): void`
- `inspect(string $prefix): array` — every namespace under the prefix, with
  held payloads, exactly as stored
- `purge(string $prefix): void`

Behavioral bar:

1. **Derived, never durable.** SQL is the record; Iris holds rebuildable
   context only. A cold, empty Iris must never break the application —
   every read path already falls back to rebuilding from SQL.
2. **TTLs are real.** Expired context is gone on read (`session:` context
   is TTL-capped by design; `user:{id}:recommendation_context` expires and
   rebuilds).
3. **`inspect` is the transparency surface.** The memory dashboard and the
   export show its output verbatim to the visitor; it must return the
   stored payloads, not summaries or internal representations.
4. **`purge` + read-back is the deletion contract.** After `purge(prefix)`,
   `inspect(prefix)` must return `[]` — deletion and pause verify this and
   throw rather than complete silently. Note the two prefix families:
   `user:{id}:` and per-trail `trail:{id}:` (not nested; callers enumerate
   trail prefixes explicitly), plus `session:{id}:` for anonymous context.
5. **Namespace isolation.** `session:` context must never be linkable to
   `user:` context; nothing may leak across prefixes.

## How to swap

1. Obtain official vendor docs; implement `app/Services/Nexus/Live/*` or a
   live `RedisIrisMemory` — thin translation layers only, no application
   logic.
2. Add the `match` arm in `ArchiveServiceProvider`, the config keys, the
   env names (1Password + `.env.example` placeholders +
   `EnvironmentValidationService`).
3. Run the contract-behavior tests against the live driver where feasible
   (the Nexus fake's tests in `tests/Feature/Nexus/` and the Iris behavior
   pinned across `tests/Feature/Memory/` are the checklist).
4. `tests/Feature/SmokeTest.php` green on the live drivers = the demo runs
   live. No application file outside the seams should have changed.
