# Phase 3 Auth Plan — Fortify + Socialite (document only)

Written during Phase 1 per the phase prompt. No auth packages are installed or configured yet. Read with `docs/STRATEGY.md` §5/§11 and `docs/PHASE-0-AUDIT.md` §4.

## Premise

Login is the **consent boundary for durable memory** (CLAUDE.md), not a generic account feature. The registration and login screens are where the memory contract is stated in plain language; `MemoryConsent` is created at registration as an explicit, versioned record (`config('memory.consent_version')`).

## Current state (from the Phase 0 audit, still true)

- Front end has zero auth routes; stock `App\Models\User`; `Authenticate`/`RedirectIfAuthenticated` middleware exist but are unrouted.
- Twill admin auth (`twill_users`, separate host via `ADMIN_APP_URL`) is fully independent — Phase 3 must not touch it.
- Sanctum is installed but unused; irrelevant to this plan.

## Fortify integration points

1. **Install**: `composer require laravel/fortify:^1.0`, publish config/actions, register `FortifyServiceProvider` in `bootstrap/providers.php`.
2. **Features**: registration, login, logout, password reset. Defer 2FA, email verification, and profile-update features until a later pass (keep the consent surface small).
3. **Views**: Fortify is headless — build `resources/views/site/auth/{login,register,forgot-password,reset-password}.blade.php` by hand into `layouts/main`, using design tokens. The register view carries the `MemoryConsentBanner` component: exactly what will be remembered, what will never be collected (no background profiling), and links to `/methodology`.
4. **Actions**: `app/Actions/Fortify/CreateNewUser.php` creates the user **and** their `MemoryConsent` row (consent version, granted_at) in one transaction — registration *is* consent grant. Login/logout otherwise stock.
5. **Route precedence**: the starter's Pages catch-all (`/{slug}` in `routes/web.php`, registered inside `App::booted()`) can shadow same-path GET routes registered later. Fortify registers its routes in its service provider (before `App::booted()` runs), so `/login`, `/register`, etc. should win — **verify with `php artisan route:list` immediately after install**; if shadowed, disable Fortify route registration (`Fortify::ignoreRoutes()`) and declare them explicitly above the catch-all.
6. **Response cache**: auth routes and everything consented must bypass the `cache:public;…` middleware group — auth pages join the uncached group; memory routes get `auth` + a new `consented` middleware (checks `ConsentService`, honors pause state).
7. **Redirects**: post-login → `/account/trails` (or intended URL); post-registration → a one-time "memory is now on" confirmation, then `/archive`.

## Socialite (optional per charter — decision open)

- Open question from Phase 0 (§12.3): whether social login makes the prototype at all, and which provider(s). Email/password alone fully satisfies the consent model. Recommendation: defer unless a demo stakeholder needs one-click login; if adopted, one provider (Google) only.
- If adopted: `composer require laravel/socialite:^5.0`; add a `social_accounts` table (user_id, provider, provider_id, unique per provider+id) rather than nullable-password users; OAuth callback runs the same `CreateNewUser` + consent path for first-time logins — **social login never bypasses the consent screen**: the OAuth return lands on a consent confirmation step before the user record is created.

## Secrets → 1Password

| Variable | Placement |
|---|---|
| `SOCIALITE_GOOGLE_CLIENT_ID` / `SOCIALITE_GOOGLE_CLIENT_SECRET` (per provider) | **1Password-only** — `op://intelligent-archive/socialite-google-{env}/client-id` etc. in `.env.1password`; platform secrets in production; empty placeholders in `.env.example` |
| `SOCIALITE_REDIRECT_BASE` | Placeholder-safe in `.env.example` |
| Fortify itself | No secrets beyond the existing `APP_KEY` |

Config access via `config/services.php` (Socialite's convention) — values still flow from env only; `EnvironmentValidationService` gains a `socialite` context required only when a provider list is non-empty.

## Model changes

- `User`: add `memoryConsent()` (hasOne), `trails()` (hasMany, Phase 4), `personalizationSetting()` (hasOne); no column changes needed for Fortify's default table.
- New (Phase 3 scope): `memory_consents` and `personalization_settings` migrations per STRATEGY.md §3.

## Tests to write in Phase 3

Registration creates user + consent atomically; consent version recorded; login/logout; guests blocked from `/account/*`; paused memory blocks writes but not reads; consent revocation flow (triggers Phase 5 deletion when Iris exists); route-precedence regression test for `/login` vs the pages catch-all; auth pages never response-cached.
