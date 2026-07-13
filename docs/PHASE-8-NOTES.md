# Phase 8 Notes — Polish, Eval, Docs

Built 2026-07-13 on branch `phase-8-polish` (stacked on `phase-7-flows`).
All tests green (185, up from 173), PHPStan clean. Implements
docs/PHASE-8-POLISH-PLAN.md per STRATEGY §356. Success bar met: **a
demoable prototype with honest docs** — the §12 demo has a seeder, a
script, a smoke test that walks all of it, and a methodology page whose
every claim the suite verifies.

## What was built

1. **`/methodology`** — dedicated route in the public-cache group ahead of
   the pages catch-all (it was a dead link into the catch-all before), an
   invokable controller and a static view stating only what the codebase
   proves: the enumerated-inputs list ("the list above is the whole
   list"), the visitor-blind knowledge layer ("nothing to delete — by
   design, not by cleanup"), the suggestion record, citations-over-vibes,
   and pause/export/delete semantics. Both hardcoded links now use
   `route('methodology')`; the footer links it site-wide.
2. **Interface-language pass** — the global header and footer existed only
   as starter scaffolding: no way for a signed-in visitor to reach their
   trails or memory, no sign-in link for guests, duplicate `<h1>` on every
   page. Both built quietly (header: The Archive · Explore · Your trails ·
   Your memory · Sign in/out; footer: How memory works · About · NGA
   credit). Vocabulary drift fixed ("Add to trail" → "Save to trail"
   family, including the accept-suggestion button and flash); the login
   page no longer claims signing in "turns memory back on" (pause
   survives sign-in); export/revoke pages now name the suggestions they
   carry/delete; empty states added (artists search, paused-with-no-trails);
   "Recently added" reframed to "From the collection".
3. **Accessibility pass** — grid images marked decorative where the title
   sits beside them (screen readers were announcing every card twice); the
   artwork detail page stops double-announcing assistive text (alt falls
   back to the title when a figcaption shows the assistive text); every
   repeated-label button (Remove/Delete/Dismiss/Save to this trail) and
   every compare checkbox carries an aria-label naming the work; invalid
   form-in-span markup fixed; the artists search input got an accessible
   name. The starter's skip link and `main` landmark were already in place.
4. **CuratedSets capsule** — hand-picked artwork groupings (title,
   editorial description, artwork browser with editorial ordering), a
   Curation section in the CMS nav, rendered as quiet strips on `/archive`.
   Published sets with no displayable works render nothing.
5. **Demo content + script** — `DemoContentSeeder` builds the demo visitor
   through the real services (ConsentService, TrailService, NoteService),
   so the ledger, item origins and held context are genuine. Idempotent,
   refuses production, fails loudly on an under-populated archive.
   `docs/DEMO-SCRIPT.md` is the §12 click-path with the consent beats
   called out.
6. **Performance contract** — `PerformanceTest` pins three things: trail
   page query count does not grow with item count, compare page query
   count does not grow with works count, and the public cache covers
   exactly the anonymous archive surfaces (never explore, account or
   compare). The flat-count tests caught a real per-artwork `artwork_slugs`
   N+1 (see findings) and a per-work trail-membership query on compare.
7. **Smoke suite** — one test walking register → consent → trail → note →
   expand → accept → dismiss → suppression-on-re-expand → compare →
   export → pause (reads open, writes 403) → resume → delete → purge
   verification → receipt. If it is green, the demo runs.
8. **Docs** — `RUNBOOK.md` (setup, import, queue worker, demo seed,
   verification, environment notes) and `VENDOR-SWAP.md` (the contract
   seams, the behavioral bar the fakes set — visitor-blindness,
   citations-or-nothing, hard exclusions, determinism, TTL/inspect/purge
   semantics — and the swap procedure).

## Findings and gotchas

- **Twill's slug accessor is a stealth N+1.** `$artwork->slug` lazy-loads
  the `slugs` relation per model; every artwork-card link was one extra
  query. The flat-query-count tests caught it on their first run — eager
  `'slugs'` added at every site where artworks feed views.
- **`db:seed` runs seeders unguarded**, which flips Twill's
  `publishedInListings` into believing a `public` column is fillable and
  querying it (undefined column on Postgres). `Model::reguard()` at the
  top of the seeder; all writes go through fillable attributes anyway.
- **`X-Cacheable` is internal.** The cache middleware sets it but the
  cookie-cleaning layer strips it; the observable cache contract is
  `Cache-Control`, which is what the tests assert.
- **Single-instance staleness in long feature tests**: `actingAs` reuses
  one `User` instance across requests, so `isPaused` read a memoized
  `personalizationSetting` relation from before the pause. `$user->refresh()`
  at the pause/resume transitions; a real browser re-authenticates fresh
  every request, so this is a test artifact, not an app bug.
- **A pinned assertion caught an over-promise, working as intended**: the
  old login copy claimed signing in turns memory back on — paused memory
  stays paused. Copy and its pinned test updated together.

## Deviations, and why

1. **Methodology is a code-versioned Blade view, not a Twill page**
   (STRATEGY's route table sketched "static editorial page"). The page
   states guarantees the code makes true; versioning it with that code
   means it can never silently over- or under-promise. Editorial pages
   remain available for everything else.
2. **FeaturedTrails deferred.** Publishing a visitor's trail requires an
   explicit, recorded publishing-consent mechanism (STRATEGY §156's own
   condition) that does not exist yet — exactly the kind of thing that
   must not be rushed into a polish phase. CuratedSets carries the
   editorial-curation weight for the demo.
3. **Stretch items stay deferred**: `MotifTrace` + "Trace this motif"
   (still no consuming surface) and enrichment generation behind the Twill
   review block (belongs with the live-vendor work).
4. **The demo visitor uses the factory password** — documented in the
   runbook, guarded out of production, and not a secret in the 1Password
   sense.

## Exact commands

```sh
# Verification:
vendor/bin/phpunit                  # 185 tests, 889 assertions
composer phpstan                    # clean

# One new migration set this phase (curated_sets + pivot):
op run --env-file=.env.1password -- php artisan migrate

# Demo content:
op run --env-file=.env.1password -- php artisan db:seed --class=DemoContentSeeder
```

No new env vars. No secrets anywhere in this phase.

## What could come next

- Live vendor clients per `docs/VENDOR-SWAP.md`, when official docs are in
  hand — the only remaining gap between this and the live §12 demo.
- FeaturedTrails with a real publishing-consent flow (explicit, recorded,
  revocable — and a snapshot, never live memory).
- `MotifTrace` + "Trace this motif"; enrichment generation behind the
  pending→approved review block.
- Socialite login, if ever revisited, must land on the consent step first
  (see memory note from Phase 3).
