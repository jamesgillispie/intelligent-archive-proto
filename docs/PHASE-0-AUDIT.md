# Phase 0 — Starter Kit Audit

Read-only audit of the AREA 17 Twill starter kit as found in this repository (2026-07-09). Companion to `docs/STRATEGY.md` (§1 summarizes; this document is the full record). No code was modified. Goal: a developer can start Phase 1 from this document without re-inspecting the codebase.

---

## 1. Versions and dependencies

### Platform

| Component | Version | Source | Flag |
|---|---|---|---|
| PHP | `^8.4` | `composer.json`; container `ghcr.io/luislavena/hydrofoil-php:8.4` (`compose.yaml`) | Current. |
| Laravel | **v11.48.0** installed (`^11.0`) | `composer.lock` | ⚠️ Laravel 12 shipped Feb 2025; per the published support schedule Laravel 11 security fixes end ~March 2026, i.e. likely already past EOL. Not a Phase 1 blocker for a prototype, but schedule an upgrade (see §9 recommendations). |
| Twill | **3.5.3** (`^3.5.3`) | `composer.lock` | Current 3.x line. Fine. |
| Node / npm | engines `node >=20`, `npm >=10` | `package.json` | Fine. |
| PostgreSQL | 17 (`bitnamilegacy/postgresql:17`) | `compose.yaml` | Fine (recently bumped per git history). `bitnamilegacy` image is a deprecation-era alias — cosmetic risk only for local dev. |
| Dev runtime | Docker Compose: `app` (overmind → `Procfile.dev`: `php artisan serve` + `npm run dev`), `pgsql`, `mailpit`; OrbStack domain labels | `compose.yaml`, `Procfile.dev` | `version: "3.9"` key is obsolete in Compose v2 (harmless warning). **No Redis service exists.** |

Command convention (from `AGENTS.md`): run everything as `docker compose run --rm --no-deps app <command>`.

### Composer packages (project-relevant)

| Package | Constraint | Role | Flag |
|---|---|---|---|
| `area17/twill` | ^3.5.3 | CMS | — |
| `area17/twill-image` | ^2.0 | Responsive/lazy images | Use for all artwork rendering |
| `area17/blast` | ^1.10 | Storybook for Blade components | Optional but useful for archive components |
| `area17/vitrine-ui` | ^3.0.2 (git repo source) | A17 Blade UI library (`<x-vui-*>`) | Private git source — needs repo access on fresh installs |
| `cwsdigital/twill-metadata` | ^1.4 | SEO metadata trait | Reuse on Artwork/Artist |
| `laravel/sanctum` | ^4.0 | API tokens | Installed, **unused** (no routes reference it) |
| `laravel/tinker` | ^2.9 | REPL | — |
| `guzzlehttp/guzzle` | ^7.8 | HTTP client | Reuse for NGA snapshot fetch + IIIF validation |
| `spatie/laravel-sitemap` | ^7.0 | Sitemap | Extend for archive routes later |
| `gehrisandro/tailwind-merge-laravel` | ^1.2 | Tailwind class merging | — |
| Dev: `laravel/pint`, `friendsofphp/php-cs-fixer` ^3.40, `larastan/larastan` ^2.9.7, `phpstan/phpstan` ^1.10.46, `phpunit/phpunit` ^10.5, `barryvdh/laravel-ide-helper` ^3.1 (+ Twill model hook), `laravel/sail`, `spatie/laravel-ignition` | | Quality tooling wired via `tools/linters.sh` + composer scripts (`lint`, `format`, `phpstan`) | PHPStan 1.x / Larastan 2.x / PHPUnit 10 are one major behind current; upgrade opportunistically with the Laravel 12 move, not before Phase 1 |

**Not installed:** Livewire, Flux, Fortify, Socialite, Horizon, any Redis client package (relies on `ext-redis`/phpredis per `config/database.php:143` — see §5), any CSV library.

### npm packages (project-relevant)

- **Runtime**: `@area17/a17-behaviors` (JS interactivity pattern), `@area17/a17-helpers`, `@area17/a17-tailwind-plugins` ~4.0, `swup` ~4.8 + plugins (a11y, body-class, head, preload, scripts) — **the site uses swup page transitions** (the `#pjax-wrapper` in `resources/views/layouts/main.blade.php`), `focus-trap`, `body-scroll-lock-upgrade`, `wicg-inert`.
- **Build/quality**: `vite` ~6.4.1, `laravel-vite-plugin` ~1.2, `tailwindcss` ~3.4.7, eslint 9 flat config, prettier (+ blade & tailwind plugins), stylelint 16, husky + lint-staged pre-commit hooks (`package.json` `lint-staged` block).
- Flags: Tailwind is pinned to 3.x by the A17 plugin suite — **do not** attempt Tailwind 4. Swup matters for us: Livewire and swup both manipulate the DOM lifecycle; see §3.

---

## 2. Starter kit conventions (later phases follow these)

1. **Twill Capsules, not app-level modules.** Every content type is a self-contained capsule in `app/Twill/Capsules/{Name}/` with `Models/` (model + `{Name}Slug` + `{Name}Revision`), `Repositories/`, `Http/Controllers/` (Twill admin controller), `Http/Requests/`, `Database/migrations/`, `Database/Seeds/`, and `routes/twill.php`. Reference anatomy: `app/Twill/Capsules/Articles/`. Existing capsules: Pages, Homepages, Articles, ArticleTypes, AboutLandings, AboutTimelineItems, ContactLandings.
2. **Capsule registration** in `config/twill.php` → `capsules.list` (name + enabled). CMS dashboard modules also registered there (`dashboard.modules`). CMS navigation built in `app/Providers/TwillServiceProvider.php::navigation()` with `TwillNavigation::addLink(...)` — singletons via `forSingleton()`, modules via `forModule()` with nested children.
3. **Model style**: extend `A17\Twill\Models\Model`; compose behaviors `HasSlug`, `HasBlocks`, `HasMedias`, `HasRevisions`, `HasRelated`, `HasMetadata` (twill-metadata); `$slugAttributes`; crops declared via `app/Support/Crops.php` constants through `getMediasParams()`; casts array. See `app/Twill/Capsules/Articles/Models/Article.php`.
4. **Front-end delivery**: plain controllers in `app/Http/Controllers/` (one per page type), using repository/model query scopes (`publishedInListings()`), validated request filters, pagination — see `ArticlesController.php`. Metadata set via `SetsMetadata` trait (`app/Http/Controllers/Traits/SetsMetadata.php`).
5. **Routing**: all front routes declared inside `App::booted()` in `routes/web.php`, wrapped in a response-cache middleware group `cache:public;max_age=60;s_maxage=3600` (`app/Http/Middleware/SimpleCacheHeader.php`, `CleanCacheableResponses.php`). Catch-all `/{slug}` for Pages is **last** — new archive routes must be registered before it. `ForceFrontRoutes` middleware exists for admin/front host separation (Twill admin on `ADMIN_APP_URL`, e.g. `admin.<site>.test`).
6. **Views**: one shell layout `resources/views/layouts/main.blade.php` (SEO block, skip-link, `<x-navigation.header>`, swup `#pjax-wrapper`, `@yield('content')`, footer section, modal stacks, icon sprite). Page templates in `resources/views/site/*.blade.php`; blocks in `resources/views/site/blocks/`; shared components in `resources/views/components/{navigation,primitives,sections,helpers,composers}`; Twill form partials in `resources/views/twill/`.
7. **JS**: A17 behaviors — small modules in `resources/frontend/scripts/behaviors/`, auto-discovered via `vite.config.js` (`BEHAVIORS_PATH` env plugin), bound to DOM with `data-behavior` attributes. No SPA framework, no Alpine as a package (the kit is "Alpine-like" via behaviors, not actually Alpine).
8. **Design tokens**: `frontend.config.json` is the single source (breakpoints sm→xxl, 12-col grid, gutters, spacing scaler 4, typesets `f-display-01`/`f-heading-01`/`f-body`/`f-ui-01`, color tokens, radii, shadow), consumed by `tailwind.config.cjs` through `@area17/a17-tailwind-plugins`. New archive styles extend this file, not raw Tailwind values. Content globs already include vitrine-ui vendor views.
9. **Support classes**: `app/Support/{Blocks,Constants,Crops,Ratios}.php` + global helpers `app/Support/helpers.php` (composer-autoloaded).
10. **Quality gates**: `composer lint` / `composer format` / `composer phpstan`; husky pre-commit runs prettier/eslint/stylelint/pint on staged files; `phpstan.neon` + `pint.json` at root.
11. **Naming**: capsule names plural PascalCase; models singular; Blade components kebab (`x-navigation.header`); behaviors camelCase files.

---

## 3. Frontend stack reality check

**Livewire: not installed. Flux: not installed.** (`composer.json` — the strings appearing in `composer.lock` are other packages' suggests/conflicts.) The kit is behaviors-first (A17's Alpine-equivalent), with swup page transitions.

**Cost of adding Livewire + Flux:**

- **Livewire (free)**: moderate friction. (a) Swup swaps DOM on navigation — Livewire components must re-initialize after swup page views (solvable with a small behavior calling `Livewire.rescan()`/morph hooks, but it's real integration work and a known class of bugs). (b) Two interactivity idioms coexist (behaviors + Livewire), so we need a rule for which owns what. (c) Response-cache middleware must never cache Livewire endpoints (they're POSTs; low risk, but the archive pages themselves must not cache per-user save states — anonymous/consented variants need separate cache treatment anyway).
- **Flux Pro (paid)**: high friction, low value here. Requires composer auth credentials (a managed secret, complicating the 1Password story and CI), brings its own design system that would fight vitrine-ui + `frontend.config.json` tokens, and pushes toward SaaS-dashboard idioms the charter explicitly rejects ("the interface should feel cultural, not SaaS").

**Would Blade + behaviors serve Phases 1–4 equally well?** Yes — those phases are browse/detail/filters/forms plus trail CRUD, all of which the starter's pattern handles natively (the Articles filter flow in `ArticlesController` is exactly the archive-browse shape). The genuinely stateful surfaces (TrailPicker, MemoryDashboard, SemanticExplorePanel, CompareWorksPanel) arrive in Phases 4–7.

**Recommendation:**

- Phases 1–3: **Blade + behaviors only.** No new frontend packages.
- Phase 4: **add `livewire/livewire` ^3** for the five consent-gated stateful components listed in `docs/STRATEGY.md` §5, with a documented swup-reinit behavior and a "Livewire only for consented write-surfaces" rule.
- **Never add Flux** for this prototype. Style Livewire components with existing tokens/vitrine-ui.
- Fallback position: if the swup+Livewire integration proves painful in Phase 4, the same components are implementable as behaviors + JSON POST endpoints — the route/controller design in STRATEGY.md doesn't change either way.

---

## 4. Auth state

**Today:**

- Front end: **no auth at all.** `routes/web.php` has zero auth/guest routes; default `App\Models\User` (`app/Models/User.php`) with the stock users migration (`database/migrations/0001_01_01_000000_create_users_table.php`); `auth.php` config is stock (`web` guard → users provider). Middleware `Authenticate` / `RedirectIfAuthenticated` exist (`app/Http/Middleware/`) but nothing routes through them.
- Twill admin: separate `twill_users` guard/tables managed by the Twill package on the admin host — **unaffected by anything we add**.
- Sanctum: installed with its migration (personal access tokens) but unused. Harmless; ignore (or remove during a later cleanup).

**What Fortify adds:** headless auth backend (registration, login, password reset, optional 2FA) with no published views — we hand-build Blade views into `layouts/main.blade.php`, which is exactly what we want since **the registration/login screens are the consent boundary** and must present memory terms (charter). No conflicts: Fortify registers its own routes/actions; nothing in the starter kit occupies `/login`, `/register` (the Pages catch-all `/{slug}` would shadow them if Fortify routes registered later — register Fortify routes normally; they're added before `App::booted()` route registration, but **verify route precedence in Phase 3** and if needed declare them explicitly before the catch-all).
**What Socialite adds:** OAuth login (optional per charter). Needs provider credentials (1Password-only), a `socialite_accounts`-style linkage table or nullable-password users, and a decision on which providers (open question §10). No conflicts with Fortify (standard combination).

---

## 5. Infrastructure config

| Concern | Current state | Evidence | Notes for us |
|---|---|---|---|
| Database | `pgsql` in `.env.example`; config default falls back to `sqlite` (`config/database.php:18`); pgsql block at `:84`; Postgres 17 container with dev creds in `compose.yaml` | confirmed PostgreSQL path | JSONB for `source_records.payload` is available. Compose dev creds are fine locally; staging/prod come from platform secrets |
| Queue | `.env.example`: `QUEUE_CONNECTION=sync`; config default `database` (`config/queue.php:15`); `jobs` table migration exists | no worker process in `Procfile.dev` | Phase 2 moves queues to Redis + adds a worker process (see §9) |
| Redis | Full client config present (`config/database.php:142` — `phpredis` default, `default`+`cache` connections) but **no Redis container, no REDIS_* in `.env.example`, and `ext-redis` availability in the hydrofoil image is unverified** | — | Phase 2: add Redis service; **verify `php -m | grep redis` in the container**; if absent, `composer require predis/predis` and set `REDIS_CLIENT=predis` |
| Cache | `.env.example`: `CACHE_DRIVER=file` (note: Laravel 11 reads `CACHE_STORE` — `config/cache.php:17`; the example var name is stale, actual default `database` applies) | ⚠️ minor drift | Fix the var name when we touch `.env.example`; move cache to Redis in Phase 2 |
| Session | `SESSION_DRIVER=file` | `.env.example` | Fine for now; consider `redis` in Phase 5 when sessions matter for anonymous explore context |
| Storage | default `local` disk (`config/filesystems.php:15`); Twill media via Glide (`GLIDE_*` in `.env.example`); S3 vars scaffolded but unused | — | NGA snapshots to `storage/app/nga/`; artwork imagery stays remote via IIIF (we hotlink NGA's IIIF endpoints, validated; no bulk image download in pass one) |
| Mail | Mailpit container (`:1025`/`:8025`), `MAIL_*` in `.env.example` | `compose.yaml` | Fortify password-reset emails work out of the box locally |
| Vite | `vite.config.js`: laravel plugin (entries `resources/frontend/{styles/app.css,scripts/app.js}`), behaviors env plugin, eslint plugin, dynamic import, manifest SRI, TLS detect from `VITE_APP_URL`; aliases `@`, `@root`, `@vitrineUI`, `@vitrineUIComponents` | — | No changes needed until Livewire (Phase 4, no Vite change required) |

---

## 6. Environment surface

- **Env files present**: `.env` (local, gitignored) and `.env.example` only. Key sets are **identical** (verified by name-only diff; values not inspected).
- **`env()` calls outside `config/`**: **none.** The only match repo-wide is the Blade `@env(['local', 'dev', 'internal'])` directive in `resources/views/layouts/main.blade.php:61` (an environment *name* check, not a variable read). The starter kit already complies with the charter's typed-config rule.
- **Variables the app currently expects** (union of `.env.example` — the practical surface — and `env()` reads in `config/`):
  - App: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL`, `VITE_APP_URL`, `ADMIN_APP_URL` (read by Twill vendor config), locale/timezone/maintenance vars (config-only, defaulted).
  - Database: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (+ config-only `DB_URL`, charset, etc.).
  - Session/cache/queue: `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_SECURE_COOKIE`, `CACHE_DRIVER` (stale name; Laravel 11 reads `CACHE_STORE`), `QUEUE_CONNECTION`.
  - Redis (config-only today, no `.env.example` entries): `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`, `REDIS_DB`, `REDIS_CACHE_DB`, `REDIS_QUEUE`, `REDIS_PREFIX`, …
  - Storage/media: `FILESYSTEM_DISK` (config-only), `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `FILE_LIBRARY_ENDPOINT_TYPE`, `MEDIA_LIBRARY_IMAGE_SERVICE`, `GLIDE_*` (4 vars).
  - Mail: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`.
  - Tracking/misc: `AXEPTIO_CLIENT_ID`, `GA` (+ config `GA4_MEASUREMENT_ID`), `CLARITY_TRACKING_ID`, `SITEMAP_STATIC`, `BLAST_ENABLED`/`STORYBOOK_SERVER_HOST` (config-only), logging channel vars (config-only, defaulted).
  - Leftover template oddities (config-only, safe to ignore): `MAIL_ORDER_CONFIRMATION_*`, `USE_LAMBDA_STORAGE`, Slack log vars.

---

## 7. 1Password readiness

**Nothing exists**: no `.env.1password`, no `op run` wrappers, no 1Password references anywhere in the project (verified by grep; only false positives in `node_modules`/`vendor`).

**Proposed local workflow** (charter-compliant, works with the Docker setup):

1. **Commit `.env.1password`** containing the *full* runtime env: non-secret keys with literal values, secret keys as `op://<vault>/<item>/<field>` references. One item per environment (`local`, `staging`, `production`) in the project vault; local uses the `local` item.
2. **Committed `.env` / `.env.example` stay placeholders only.** Real values never land in the repo.
3. **Secret pass-through for the container.** Laravel gives real process env vars precedence over `.env` file entries, so declare secret variable *names* (no values) under the `app` service's `environment:` key in `compose.yaml` (e.g. `- NEXUS_API_KEY`). Then host-side `op run` injects them through Compose into the container.
4. **Wrapper commands** (document in README; optionally add a `tools/op.sh` or Make targets later):
   - Artisan/imports: `op run --env-file=.env.1password -- docker compose run --rm app php artisan nga:import`
   - Queue worker: `op run --env-file=.env.1password -- docker compose run --rm app php artisan queue:work redis`
   - Dev stack: `op run --env-file=.env.1password -- docker compose up`
5. **Verification without disclosure**: `EnvironmentValidationService` (Phase 2) checks presence/format of required keys per context and fails naming the variable, never echoing values. For manual checks: `op run --env-file=.env.1password -- printenv | grep -c NEXUS_` (counts, not values).
6. **Production has no `op` dependency**: platform secrets populate the same variable names; `.env.1password` is a local/staging convenience, and the typed config files are the only consumers either way.

---

## 8. Reuse / extend / avoid

**Reuse as-is**
- Capsule architecture + `Articles` capsule as the module template (`app/Twill/Capsules/Articles/`).
- Controller/route/response-cache pattern (`ArticlesController`, `routes/web.php`, `SimpleCacheHeader`).
- `layouts/main.blade.php` shell, vitrine-ui components, component directories.
- Behaviors JS pattern + swup transitions; `frontend.config.json` token system.
- `Crops`/`Blocks`/`Ratios` support classes; `SetsMetadata` + twill-metadata.
- Guzzle for NGA HTTP; twill-image for artwork rendering.
- Quality toolchain (Pint/php-cs-fixer/PHPStan/PHPUnit, husky hooks, `tools/linters.sh`) and Docker/overmind workflow.
- Blast/Storybook for developing archive components in isolation.

**Extend**
- `compose.yaml`: add Redis service; add secret pass-through names; (cosmetic) drop obsolete `version:` key.
- `Procfile.dev`: add a queue worker process (Phase 2).
- `.env.example`: add NGA/NEXUS/REDIS_IRIS/MEMORY/TRAILS/REDIS placeholder keys; fix `CACHE_DRIVER`→`CACHE_STORE`.
- `config/twill.php` + `TwillServiceProvider::navigation()`: register archive capsules and Archive/Curation/Imports nav sections.
- `frontend.config.json`: archive typography/color tokens as the visual language develops.
- `app/Models/User.php`: consent + memory relationships (Phase 3–4).
- `tests/`: real suites replacing the two example tests.
- `spatie/laravel-sitemap` setup: archive routes later.

**Avoid**
- Flux Pro (paid, secret-bearing, design-system conflict) — see §3.
- Any `env()` outside `config/` (kit is currently clean; keep it that way).
- Putting archive logic in demo capsules; leave Pages/Articles alone until a deliberate cleanup phase.
- Tailwind 4 or replacing the A17 plugin pipeline.
- Building queue-dependent features on `sync`; blind full-dataset imports; touching Twill's admin auth.
- Editing `resources/views/vendor/` or vitrine-ui vendor files directly.

---

## 9. Pre-Phase-1 package plan (commands to run later — none were executed)

Phase-tagged; all through the container per `AGENTS.md`:

```sh
# Phase 2 (ingestion)
docker compose run --rm --no-deps app composer require league/csv:^9.0
# only if ext-redis is missing from the container (verify first: php -m | grep redis):
docker compose run --rm --no-deps app composer require predis/predis:^2.3

# Phase 3 (auth/consent)
docker compose run --rm --no-deps app composer require laravel/fortify:^1.0
docker compose run --rm --no-deps app composer require laravel/socialite:^5.0   # optional, if social login confirmed

# Phase 4 (trails UI)
docker compose run --rm --no-deps app composer require livewire/livewire:^3.0
```

No npm installs required for Phases 1–4. No package **upgrades** before Phase 1: the Laravel 11→12 (+PHPStan 2/Larastan 3/PHPUnit 11) move is recommended but should be its own isolated maintenance branch — doing it mid-prototype-setup risks Twill/vitrine-ui compatibility churn for no prototype value. Decide timing at Phase 0 sign-off (open question below).

---

## 10. Project environment variable inventory

New variables, grouped. **Placeholder-safe** keys ship in `.env.example` with dummy/default values and get literal values in `.env.1password`; **1Password-only** keys appear in `.env.example` as empty placeholders (`NEXUS_API_KEY=`) and only ever hold real values as `op://` references / platform secrets.

| Group | Placeholder-safe in `.env.example` | 1Password-only |
|---|---|---|
| Core additions | `REDIS_HOST=redis`, `REDIS_PORT=6379`, `QUEUE_CONNECTION=redis` (Phase 2+), `CACHE_STORE=redis` (Phase 2+) | `REDIS_PASSWORD` (empty locally) |
| `NGA_*` | `NGA_OPENDATA_REPO_URL` (public GitHub URL), `NGA_SNAPSHOT_REF` (commit SHA/`main`), `NGA_SNAPSHOT_DISK=local`, `NGA_IIIF_TIMEOUT`, `NGA_IIIF_RATE_LIMIT_PER_MIN`, `NGA_IMPORT_SUBSET=paintings`, `NGA_IMPORT_CHUNK_SIZE` | — (all public data; no secrets) |
| `NEXUS_*` | `NEXUS_DRIVER=fake` (`fake`\|`live`), `NEXUS_INDEX_NAME`, `NEXUS_TIMEOUT`, `NEXUS_RETRIEVAL_LIMIT` | `NEXUS_API_KEY`, `NEXUS_ENDPOINT` (account-specific), any org/project id the real SDK needs (finalize when docs are in session) |
| `REDIS_IRIS_*` | `REDIS_IRIS_DRIVER=fake` (`fake`\|`live`), `REDIS_IRIS_KEY_PREFIX`, `REDIS_IRIS_DEFAULT_TTL`, `REDIS_IRIS_ANON_SESSION_TTL` | `REDIS_IRIS_URL`/host+port, `REDIS_IRIS_API_KEY`/password (finalize when docs are in session) |
| `MEMORY_*` | `MEMORY_CONSENT_VERSION`, `MEMORY_EXPORT_FORMAT=json`, `MEMORY_ACTIVE_TRAIL_TTL_DAYS`, `MEMORY_ANON_EXPLORE_RATE_LIMIT` | — |
| `TRAILS_*` | `TRAILS_MAX_PER_USER`, `TRAILS_MAX_ITEMS`, `TRAILS_EXPANSION_MIN_ITEMS=3`, `TRAILS_SUGGESTIONS_PER_EXPANSION` | — |
| `SOCIALITE_*` (if adopted) | `SOCIALITE_PROVIDERS` (comma list), `SOCIALITE_REDIRECT_BASE` | `SOCIALITE_GOOGLE_CLIENT_ID`, `SOCIALITE_GOOGLE_CLIENT_SECRET` (and per-provider equivalents) |
| Existing secrets moving to 1Password refs | — | `APP_KEY`, `DB_PASSWORD` (staging/prod), `MAIL_USERNAME`/`MAIL_PASSWORD` (non-local), `AWS_*` keys (if S3 adopted) |

Each group is consumed exclusively through its typed config file (`config/nga.php`, `config/nexus.php`, `config/redis_iris.php`, `config/memory.php`, `config/trails.php`) — created in the phase that first needs it — and validated by `EnvironmentValidationService` with per-context requirement sets (e.g. `NEXUS_API_KEY` required only when `NEXUS_DRIVER=live`).

## 11. Starter kit changes needed before coding begins

1. Commit the project charter as `CLAUDE.md` (currently only in conversation) and this document + `docs/STRATEGY.md`.
2. `compose.yaml`: add Redis service (+ volume), secret pass-through env names on `app`; optionally drop the obsolete `version:` key.
3. `Procfile.dev`: add `worker: php artisan queue:work` (Phase 2 activation).
4. `.env.example`: add the §10 placeholder keys (values dummy), fix `CACHE_DRIVER`→`CACHE_STORE`; create `.env.1password` per §7.
5. Nothing else — the kit is otherwise ready for Phase 1 capsule work as-is.

## 12. Open questions before Phase 1

1. **Laravel 12 upgrade timing** — likely past L11 security EOL. Upgrade now (delays Phase 1 by a maintenance branch) or after the prototype proves out? Recommendation: log it, proceed on 11 for the prototype, upgrade before anything public.
2. **Demo content fate** — keep Pages/Articles/About/Contact capsules for editorial reuse (STRATEGY.md assumes Pages becomes the editorial-page vehicle) or strip to a minimal shell? Recommendation: keep; decide removal in Phase 8.
3. **Socialite scope** — include social login in Phase 3 at all, and if so which provider(s)? Email/password alone satisfies the consent model; Socialite is charter-optional.
4. **vitrine-ui access** — the composer repo is a git source (`https://github.com/area17/vitrine-ui.git`); confirm fresh-install access (and whether credentials are needed) before CI or new machines matter.

None of these block starting Phase 1 under the recommendations given.
