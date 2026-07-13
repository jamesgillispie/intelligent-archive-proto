# Runbook

How to stand the prototype up, feed it, and verify it — locally and beyond.
Every command runs through the 1Password context; `.env` in the repo holds
placeholders only, and no command below ever prints a secret value.

## Local prerequisites

- PHP via Laravel Herd (no Docker in the local setup)
- PostgreSQL 17 (Homebrew) with a database matching `DB_DATABASE`
- Redis (Homebrew) for queues and cache
- Node for Vite assets
- 1Password CLI (`op`) signed in; `.env.1password` resolves
  `op://vault/item/field` references

## First run

```sh
composer install
npm install

# Verify required configuration is present (names only, never values):
op run --env-file=.env.1password -- php artisan config:show nga | head

op run --env-file=.env.1password -- php artisan migrate
npm run dev            # or: npm run build
```

Serve through Herd (or `php artisan serve`). The environment validation
service fails loudly — by variable name — when something required is
missing; that is the first thing to check on a boot failure.

## Feeding the archive

```sh
# 1. Snapshot the NGA open-data CSVs at the pinned commit SHA:
op run --env-file=.env.1password -- php artisan nga:fetch

# 2. Import the curated subset: immutable source records, then canonical
#    artworks/artists/vocabularies (idempotent; safe to re-run):
op run --env-file=.env.1password -- php artisan nga:import

# 3. Download primary images as Twill media (queued, rate-limited):
op run --env-file=.env.1password -- php artisan nga:images
```

## Queue worker

Local queues run on Redis (`QUEUE_CONNECTION=redis` via 1Password; the test
suite uses `sync`). Image downloads, indexing and import jobs all pass
through it:

```sh
op run --env-file=.env.1password -- php artisan queue:work redis --tries=3 --memory=256 --max-jobs=400 --stop-when-empty
```

Drop `--stop-when-empty` for a long-running worker. Restart workers after
deploying code changes (`php artisan queue:restart`).

## Demo content

```sh
op run --env-file=.env.1password -- php artisan db:seed --class=DemoContentSeeder
```

Creates the consented demo visitor (`demo@trails.test`, factory password),
two trails built through the real services, and a published curated set.
Idempotent; refuses to run in production; fails loudly if the archive has
fewer than 8 displayable works. The click-path lives in
`docs/DEMO-SCRIPT.md`.

## Verification

```sh
vendor/bin/phpunit        # full suite (fakes; CACHE_STORE=array, QUEUE_CONNECTION=sync)
composer phpstan          # larastan level 6
```

`tests/Feature/SmokeTest.php` walks the entire visitor journey; if it is
green, the demo runs. `tests/Feature/PerformanceTest.php` pins the
flat-query-count and cache-coverage contracts.

## Environments beyond local

- Environments are `local`, `staging`, `production`. Do not assume the `op`
  CLI exists in production — the same variable names must be populated by
  platform secrets there.
- The public HTTP cache covers only anonymous archive pages and
  `/methodology` (`cache:public;max_age=60;s_maxage=3600`); account,
  explore and compare routes must never sit behind a shared cache — the
  test suite pins this.
- Both vendor drivers ship as `fake`. Setting `NEXUS_DRIVER` or
  `REDIS_IRIS_DRIVER` to anything else fails loudly at resolution until a
  real client exists — see `docs/VENDOR-SWAP.md`.
