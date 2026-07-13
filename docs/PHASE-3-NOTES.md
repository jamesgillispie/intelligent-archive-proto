# Phase 3 Notes — Auth as the Consent Boundary

Built 2026-07-13 on branch `phase-3-auth-consent` (on the rebuilt `main`, which now roots at a single starter-kit baseline commit). All tests green (73, up from 50), PHPStan clean. Implements docs/PHASE-3-AUTH-PLAN.md with the deviations noted below.

## What was built

1. **Fortify, headless** — `laravel/fortify:^1.0` with registration, login, logout and password reset only; email verification, profile/password updates and 2FA are deferred to keep the consent surface small (the published 2FA users-table migration was removed). Auth screens are hand-built `site/auth/*` Blade views in the archive's design language.
2. **Registration is the consent grant** — `CreateNewUser` runs in one transaction: the user row, a versioned `MemoryConsent` (`config('memory.consent_version')`, `granted_at`), and default `PersonalizationSetting` are created together or not at all. The register screen carries `x-memory.consent-banner`: what memory keeps (explicit actions only), what it never records (no background profiling), and the standing rights (inspect, pause, export, delete) with a `/methodology` link and the agreement version.
3. **Consent data layer** — `memory_consents` (unique per user + version; re-consent after a wording change is a new row) and `personalization_settings` (`memory_paused`, `semantic_suggestions_enabled`); models in `app/Models/Memory`, `ConsentService` in `app/Services/Memory` answering the two gate questions: is consent current, is memory paused.
4. **`consented` middleware** — guests to login; stale or revoked consent to `/account/consent` (a page explaining that renewal ships with the memory dashboard); paused memory blocks unsafe methods with a clear 403 but never blocks reads — the user can always look at what the archive remembers.
5. **Redirects** — post-login → `/account/trails` (stub view; trail building is Phase 4). Post-registration → one-time "Memory is now on" confirmation (session-flagged; revisits go to the archive), then onward links.

## Findings and gotchas

- **Routes registered inside `App::booted()` get no middleware group.** The public pages are deliberately stateless (no session → cookie-free, cacheable responses); the starter's `private` group in `bootstrap/app.php` exists for exactly the stateful-uncached case. Account routes use `['private', 'auth', 'consented']`; forgetting this yields "Session store not set on request" 500s.
- **Route precedence held without workarounds**: Fortify registers its routes at provider boot, before the `App::booted()` Pages catch-all, so `/login`, `/register` etc. win. `Fortify::ignoreRoutes()` was not needed; a regression test locks the behavior in.
- **`X-Cacheable` is an internal marker** — `CleanCacheableResponses` strips it (plus `Set-Cookie`) after use, so the observable caching contract is `Cache-Control` + cookie absence, and the tests assert exactly that. Auth and account pages carry no `public` cache headers; the archive control test proves the cache mechanism is still active where it should be.
- Twill admin auth (separate host) is untouched; `php artisan route:list` shows the two auth systems side by side without overlap.

## Deviations, and why

1. **Pause state lives only on `personalization_settings.memory_paused`.** STRATEGY.md §3 sketches pause timestamps on both `memory_consents` and `personalization_settings`; one source of truth avoids the two drifting, and `PersonalizationSetting` is the model whose stated purpose is the memory switches. `MemoryConsent` keeps `granted_at`/`revoked_at` only.
2. **Socialite deferred** — James resolved the open Phase 0 §12.3 decision on 2026-07-13: email/password fully satisfies the consent model; no OAuth secrets, no `socialite` context in `EnvironmentValidationService`. The plan's constraint stands if revisited: the OAuth return must land on the consent step before any user record exists.
3. **Stale-consent redirect goes to a dedicated uncached `/account/consent` page** rather than flashing a notice onto `/archive` — a flashed message rendered into a `public; s-maxage` response could be cached by a CDN for everyone.
4. **A one-time session flag drives the post-registration confirmation** rather than a DB column; it is presentation state, not memory.
5. Phase 3 required no secrets after all (plan said "requires `op signin`" for Socialite secrets, which are deferred). Commits remain unsigned per local convention.

## Exact commands

```sh
# Verification:
vendor/bin/phpunit                  # 73 tests, 286 assertions
composer phpstan                    # clean
php artisan route:list | grep -E 'login|register|account|\{slug\}'
```

No new env vars; `MEMORY_CONSENT_VERSION` already existed in `config/memory.php` with a safe default.

## Phase 4 task list (trails + explicit memory writes)

1. `ResearchTrail`, `TrailItem`, `ArtworkSave`, `UserNote`, `MemoryEvent` models/migrations per STRATEGY.md §3 — every write behind the `consented` middleware and recorded as a `MemoryEvent`.
2. "Save to trail" / "Start a trail" affordances on artwork pages (Livewire decision per STRATEGY.md §5: add `livewire/livewire` ^3 free in Phase 4 — verify against the Alpine-first starter before committing).
3. `/account/trails` becomes real: list, create, continue; trail detail with items and notes.
4. Replace the trails-stub copy and the memory-activated links as the surfaces arrive.
5. Carry-overs from Phase 2: `missing_from_source_at` sweep, `nga:fetch` snapshot command.
