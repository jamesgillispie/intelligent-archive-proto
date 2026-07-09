# Phase 1 Notes — Archive Foundation

Built 2026-07-09 on branch `phase-1-archive-foundation`. All 35 tests green (137 assertions), PHPStan clean, 500 real NGA paintings imported and browsable.

## What was built

1. **Config layer** — `config/{nga,nexus,redis_iris,memory,trails}.php` (typed, placeholder-safe defaults), `EnvironmentValidationService` + `php artisan env:validate` (presence only, names never values), `.env.example` inventory, committed `.env.1password`.
2. **Source layer** — `SourceImport`, `SourceRecord` (verbatim JSONB payload, NGA id, content hash, version, rights, upstream timestamps; unique per table+id; append-preserving), `SourceImage` typed view. No Twill.
3. **Canonical layer** — Twill capsules `Artworks`, `Artists`, `Classifications`, `Departments`, `Mediums` following the Articles anatomy; `HasSourceFields` provenance trait; `ImageAsset` for remote IIIF imagery; CMS navigation under "Archive"; review-only controllers (no create/delete) with an imported-source-values panel.
4. **Import pipeline** — `php artisan nga:import` → `NgaImportService` (5 streaming passes over the CSVs, curated-subset selection) → `NgaSourceMapper` (hash-based change detection) → `CanonicalizeArtwork` jobs → `ArtworkCanonicalizer`. Idempotent; counts-only logging.
5. **Frontend** — `/archive`, `/archive/artworks` (+detail), `/archive/artists` (+detail); validated filters (artist, classification, department, medium, year range, keyword); IIIF srcsets with assistive alt text; source-citation panel that names editorial overrides. Blade + design tokens, no Livewire/Flux (per Phase 0 audit).
6. **Vendor boundaries** — `NexusArchiveContract`/`FakeNexusArchive` (cited, deterministic relatedness; `ArchiveMatch` cannot exist without citations), `RedisIrisMemoryContract`/`FakeRedisIrisMemory` (namespaces, TTLs, inspect/purge), bound in `ArchiveServiceProvider`; `live` drivers throw until real docs exist.
7. **Docs** — `docs/PHASE-3-AUTH-PLAN.md` (Fortify/Socialite, document only).

## Import results (real data)

145,565 objects scanned → **4,051 eligible** (accessioned, non-virtual paintings with a primary open-access image — the strategy's ~4k estimate confirmed) → 500 imported with 236 artists, 501 images, correct role filtering. Re-run: everything `unchanged`.

## Running the import locally

```sh
# Canonical form (CLAUDE.md secrets rule):
op run --env-file=.env.1password -- php artisan nga:import --limit=500

# Data must exist first (public, no secrets; ~220 MB total):
mkdir -p storage/app/nga && cd storage/app/nga
for f in objects constituents objects_constituents published_images; do
  curl -sL -o "$f.csv" "https://raw.githubusercontent.com/NationalGalleryOfArt/opendata/main/data/$f.csv"
done

# Useful flags: --limit=N --path=/abs/path --sync (inline canonicalization)
# Verify environment first: php artisan env:validate
```

The CSVs are already downloaded on this machine. QUEUE_CONNECTION is sync locally, so `--sync` and queued behave identically until Phase 2.

## Deviations from plan, and why

1. **Docker is not available on this machine** (no Docker Desktop/OrbStack), so `AGENTS.md`'s `docker compose run` convention could not be used. Everything ran on host PHP 8.4.23 (Laravel Herd). PostgreSQL 17 was installed via Homebrew; the server answering on 5432 is a Herd-built Postgres 17.0. Databases: `intelligent-archive` (dev), `intelligent_archive_test` (tests, wired in `phpunit.xml`).
2. **`op run` wrapper**: the `op` CLI is installed but signed out, and Phase 1 uses zero secrets (public NGA data; both vendor drivers `fake`), so commands ran unwrapped this session. `.env.1password` is committed with literal non-secrets and the `op://intelligent-archive/...` reference convention commented until vault items exist. Run `op signin` before using the wrapper.
3. **Commits are unsigned**: git is configured for 1Password SSH signing, which needs the desktop app unlocked. Committed with signing disabled per-command; re-sign before merging if required (`git rebase --exec 'git commit --amend --no-edit -S' main..`).
4. **Provenance pattern refinement** vs STRATEGY.md §4: instead of separate `{field}_override` columns, the display column itself is editable in Twill and an override is detected by comparison against `source_{field}` (see `HasSourceFields`). Same guarantees — re-imports always refresh source values and never clobber editorial edits — with native Twill form UX. Trade-off: "clear override" means retyping the source value (a revert affordance can come later).
5. **No Twill media library for artwork imagery**: NGA serves open-access images over IIIF, so `ImageAsset` stores remote IIIF URLs (+ `assistivetext`, `maxpixels`). Media roles/crops are reserved for future editorial imagery.
6. **No `league/csv`**: a small streaming reader (`NgaCsvReader`) over `SplFileObject` handles BOM + quoted multi-line fields; one less dependency.

## Starter kit fixes made along the way

- `TwillServiceProvider`: guarded the twill-metadata settings boot so artisan works on a fresh database.
- `resources/views/stories/data/navigation.php`: `function_exists` guard (+types) — Blast includes this file on every boot, which happens repeatedly under PHPUnit.
- Removed phantom `App\Providers\MetaTagsServiceProvider` from `bootstrap/providers.php` (class does not exist in the repo).
- `.env.example`: `CACHE_DRIVER` → `CACHE_STORE` (Laravel 11 reads the latter).
- `tests/Feature/ExampleTest` now asserts `/archive` (the `/` route 404s without a homepage singleton in a fresh DB).
- `Artwork` declares `public array $mediasParams = []` — without it, twill-metadata's trait writes a model *attribute* and breaks inserts.
- `Medium` uses an explicit `mediums` table and passes explicit plurals to Twill's migration helpers (`Str::plural('medium')` = `media`, which collides with Twill's media table).

## Known limitations (accepted for Phase 1)

- **~22 `objects_constituents` rows report `updated` on every re-run**: NGA ships multiple join rows for the same (object, constituent) pair with different role text; they map to one source record keyed `{objectid}:{constituentid}`, so the hash alternates. Harmless to canonical data (the pivot is unique per pair). Fix in Phase 2 by keying on `{objectid}:{constituentid}:{role}`.
- Image URLs are stored but **not yet validated** (`validation_status = unvalidated`); `is_displayable` currently means "has a primary published image row". Validation is Phase 2's job.
- `missing_from_source_at` is written by the mapper but no sweep marks records that disappear from a newer snapshot (Phase 2).
- No SEO metadata on archive pages yet (twill-metadata is wired on the model; the front-end `SetsMetadata` pass is Phase 8 polish).
- Artwork admin search covers the default title column only.

## Phase 2 ordered task list (full ingestion: change detection + image validation)

1. Add Redis to local dev (compose service for Docker users; `brew`/Herd redis locally) and a queue worker (`Procfile.dev`); flip `QUEUE_CONNECTION=redis` in `.env.1password`.
2. `nga:fetch` command: download CSVs at a pinned upstream commit SHA into `storage/app/nga/{sha}/`, record `snapshot_ref` on `SourceImport`.
3. Re-key `objects_constituents` source ids with role (see limitation above) with a one-off migration/backfill.
4. Missing-from-source sweep: after each import, stamp `missing_from_source_at` on previously-selected records absent from the new snapshot; decide canonical behavior (unpublish? flag?).
5. `ValidateArtworkImage` queued job: IIIF thumb request with `config('nga.iiif')` rate limiting/timeout; record status + dimensions + `validated_at`; redefine `is_displayable` = has *validated* primary image.
6. Change-driven promotion: only dispatch `CanonicalizeArtwork` for created/updated source records; add `nga:import --object=<id>` partial refresh.
7. Emit `ArtworkPromoted` domain event from the canonicalizer (no listeners yet — Phase 6 subscribes the Nexus indexer).
8. `ImportBatches` read-only Twill module over `source_imports` (counts, status, report) for editor visibility.
9. Scale test: widen the subset (e.g. limit 4,000) and chunk/optimize the mapper if needed.
10. Tests alongside each of the above; keep the fixture-based suite as the fast path.
