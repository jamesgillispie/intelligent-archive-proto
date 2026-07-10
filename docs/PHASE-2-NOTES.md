# Phase 2 Notes — Images and Full Ingestion

Built 2026-07-10 on branch `phase-2-images-ingestion` (stacked on `phase-1-archive-foundation`). All tests green, PHPStan clean, the full eligible NGA subset imported with downloaded imagery.

## What was built

1. **Join-key fix** — `objects_constituents` source records are keyed `{objectid}:{constituentid}:{role}` (backfilled in place by migration). The full import then surfaced a second wrinkle: NGA ships rows whose key is identical even with role included (all "related artist" joins), so the mapper now dedupes within a run — first row wins, later same-key rows are counted `duplicate_in_source` and skipped. Two consecutive runs report zero created/updated (test-proven and verified at full scale, below).
2. **Image pipeline** — `DownloadArtworkImage` queued jobs fetch each primary image over IIIF, validate the bytes (a body that doesn't decode as an image is a failure, not a file), store them on the Twill media disk at `{nga_uuid}/{nga_uuid}.jpg`, and attach them to the artwork as Twill media under the `artwork` role with a full-frame free crop — archival paintings are never cropped; the frontend letterboxes. `ImageAsset.media_id/downloaded_at/failure_reason` track state; `nga:images` resumes/backfills/retries.
3. **Full-subset import** — `nga:import --limit=4100` selected all 4,051 eligible paintings; canonicalization and downloads ran as Redis queue jobs.
4. **Import dashboard** — Twill admin → Archive → Import batches: per-batch created/updated/unchanged/duplicate counts, status, duration, primary-image summary (downloaded/pending/failed), and flagged images linking to the artwork edit screen. A custom read-only page, not a Twill module: Twill never manages the raw source layer (CLAUDE.md boundary).
5. **Frontend** — a shared `x-archive.artwork-image` component renders the best available source per work: local Twill media through Glide (`fit=max`), remote IIIF while a download is pending, quiet placeholder when nothing usable remains. Cards lazy-load; detail images are eager with `fetchpriority=high`; controllers eager-load `medias`.

## Image pipeline findings

- **URL patterns confirmed from real records + live requests** (no assumptions): `published_images.iiifurl` is an IIIF Image API 2.0 base (`https://api.nga.gov/iiif/{uuid}`); sized derivatives via `{iiifurl}/full/!{s},{s}/0/default.jpg`; `info.json` serves tiles + precomputed sizes; responses are Cloudflare-cached (`cache-control: max-age=86400`), `access-control-allow-origin: *`.
- `maxpixels` is the rights cap: empty for most works, a number (e.g. 640) for size-restricted ones. `ImageAsset::sizedUrl()` clamps every request to it.
- Download size: `!1600,1600` bounding box (`NGA_IMAGE_DOWNLOAD_SIZE`); other sizes remain derivable from the stored IIIF base URL at any time without re-scanning the dataset (the source payload keeps `iiifurl`, native `width`/`height`, `maxpixels` verbatim).
- **Failure rate: 0 of 4,059 primary images** (100% success; a few artworks carry two primary rows, hence > 4,051). Every published_images row resolved over IIIF. The failure paths (4xx flag, undecodable body flag, 5xx retry, retries-exhausted flag) are test-covered but were never exercised by the real dataset.
- **Timing at full scale**: source phase (145,565 objects scanned → 4,051 selected, ~12,900 source records) takes ~8 seconds; the wall clock is entirely the polite rate limit — `NGA_IIIF_RATE_LIMIT_PER_MIN=120` (2 req/s) drained all downloads in ~46 minutes (including worker restarts). Downloaded corpus: 1.5 GB of JPEGs at a ≤1600px bounding box.
- Failure contract: 4xx and undecodable bodies flag the asset (`failure_reason`) and never crash the batch; 5xx/timeouts throw so the queue retries (maxExceptions 3 within 6h — rate-limit releases don't burn the retry budget).

## Deviations, and why

1. **`is_displayable` still means "has a primary published image"**, not "has a validated download" (Phase 1 notes had proposed the latter). This phase's prompt requires failed-download works to keep rendering with fallbacks rather than vanish from `/archive`; the dashboard carries the validation signal instead.
2. **Downloads are dispatched from the canonicalizer**, not a separate orchestration pass: new assets queue a download; an upstream `iiifurl` change forces a refresh; previously failed assets are retried only explicitly (`nga:images --retry-failed`). Resumability falls out of state rather than bookkeeping.
3. **Twill media for artwork imagery after all** (Phase 1 kept imagery remote-only): the phase prompt asks for Twill media with capsule-defined crops. The remote IIIF metadata stays canonical on `ImageAsset`; the media file is a cache of it. One crop only, free-ratio full frame.
4. **Dashboard is a custom admin page**, not the "ImportBatches capsule" wording of the roadmap — a Twill module over `source_imports` would put Twill in charge of the source layer.
5. `op` remained signed out; Phase 2 uses no secrets (NGA images are open access). Commits are unsigned as in Phase 1 (`git -c commit.gpgsign=false`), re-sign before merge if required.

## Starter-kit fixes en route (isolated commits)

- `FILE_LIBRARY_ENDPOINT_TYPE` now defaults to `local` in `.env.example`/`phpunit.xml`: the `s3` default with empty `AWS_*` made **every** Twill admin page render throw (`A "region" configuration value is required`). One env var restores s3.

## Exact commands

```sh
# Full import (source records → queued canonicalization → queued downloads):
op run --env-file=.env.1password -- php artisan nga:import --limit=4100

# Workers (supervised loop; workers exit at the memory cap by design):
until [ "$(redis-cli llen 'intelligent_archive_database_queues:default')" = "0" ]; do
  op run --env-file=.env.1password -- php artisan queue:work redis --tries=3 --memory=256 --max-jobs=400 --stop-when-empty
  sleep 3
done

# Image backfill / resume / retry (also reports pipeline status):
op run --env-file=.env.1password -- php artisan nga:images
op run --env-file=.env.1password -- php artisan nga:images --retry-failed

# Verification:
vendor/bin/phpunit
composer phpstan
```

Local env changes this phase: `QUEUE_CONNECTION=redis` (Herd ships Redis; already running), `FILE_LIBRARY_ENDPOINT_TYPE=local`, `NGA_IMPORT_LIMIT=4100` (in `.env.1password`).

## Verification at full scale

- Full import: 145,565 objects scanned → **4,051 eligible → 4,051 imported** (the entire eligible set; the strategy's ~4k estimate was exact), 1,354 artists, ~12,900 source records.
- Images: **4,059 / 4,059 primary images downloaded, 0 failed, 0 pending**; a follow-up `nga:images` dispatched 0 jobs (resume logic confirmed — nothing re-downloads).
- Idempotency: two consecutive `nga:import --limit=4100` runs after the full import. Run A reported 1 `updated` (the single surviving exact-duplicate key converging to its first-row payload) + 1 `duplicate_in_source`; **run B reported zero created and zero updated** — every table `unchanged`, the duplicate counted but not written.
- Dashboard (Twill admin → Archive → Import batches) shows the batch rows above with durations and the 4,059/0/0 image summary.
- Suite: **50 tests, 205 assertions, green** (Phase 1's 35 tests all still pass); PHPStan clean.

## Known limitations (accepted for Phase 2)

- Alternate (non-primary) views are recorded in the source layer and on `ImageAsset` but not downloaded — the archive renders primaries only for now.
- `missing_from_source_at` sweep still pending (Phase 1 carry-over): records absent from a newer snapshot aren't yet flagged.
- `nga:fetch` (pinned-snapshot CSV download command) remains todo; imports read whatever `storage/app/nga/` holds.
- Glide renders derivatives on first request (then caches); a warm-cache pass could pre-generate grid sizes if first-paint matters for a demo.
- The dashboard shows the current pipeline state globally; per-batch image attribution would need batch ids on the download jobs.

## Phase 3 task list (auth + consent, per docs/PHASE-3-AUTH-PLAN.md)

> Phase 3 requires `op signin` — OAuth client secrets live only in 1Password (`op://intelligent-archive/socialite-*`).

1. Install Fortify (`composer require laravel/fortify`), publish config/actions, register the provider; features: registration, login, logout, password reset only.
2. Verify route precedence over the Pages `/{slug}` catch-all immediately (`php artisan route:list`); fall back to `Fortify::ignoreRoutes()` + explicit routes if shadowed.
3. Build `site/auth/*` Blade views into `layouts.main` with the memory-consent banner: registration **is** consent grant.
4. `CreateNewUser` action creates the user + versioned `MemoryConsent` row in one transaction (`config('memory.consent_version')`); migrations for `memory_consents` and `personalization_settings`.
5. Keep auth + consented routes out of the public response cache; add a `consented` middleware honoring pause state.
6. Post-login → `/account/trails` stub; post-registration → one-time "memory is now on" confirmation → `/archive`.
7. Socialite: decision still open (Phase 0 §12.3); if adopted, Google only, and the OAuth return must land on the consent confirmation step before any user record exists.
8. Tests: atomic user+consent creation, consent versioning, guest blocking, route precedence regression, auth pages never cached.
