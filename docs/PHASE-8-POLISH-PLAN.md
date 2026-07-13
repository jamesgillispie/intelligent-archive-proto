# Phase 8 Plan — Polish, Eval, Docs

Per STRATEGY §356 ("Phase 8 — Polish, eval, docs") and the task list left in
docs/PHASE-7-NOTES.md. Branch `phase-8-polish`, stacked on `phase-7-flows`.
Success bar: **a demoable prototype with honest docs** — the smallest demo
(STRATEGY §12) shows well, every surface speaks the charter's language, and
someone else could run the whole thing from the docs alone.

## Ground rules (unchanged)

- No vendor code. Redis Iris and Pinecone Nexus stay behind their contracts
  and fakes; this phase documents the swap seams, it does not implement them.
- No secrets anywhere. Runbooks show `op run --env-file=.env.1password --`
  invocations and reference variables by name only.
- The consent model is finished — nothing in this phase adds a
  personalization input or a memory write. Polish must not loosen anything.

## Tasks

### 1. Methodology page

`/methodology` is linked from the consent banner and the memory dashboard but
resolves through the Twill pages catch-all — a dead link unless an editor
happens to create the page. The guarantees belong in code, versioned with the
code that makes them true:

- Dedicated `GET /methodology` route in the response-cache group, registered
  before the `{slug}` catch-all; invokable `MethodologyController`; static
  editorial view in the site conventions.
- Content states only what the codebase can prove: the enumerated explicit
  actions (and nothing else) as personalization inputs; no per-user data ever
  sent to the archive knowledge layer; every suggestion persisted with its
  reason and evidence before it renders; deletion with read-back
  verification; export of everything, including held context; pause semantics
  (stops writes, never reads).
- Fix both hardcoded `/methodology` links to `route('methodology')`.
- Tests: renders anonymously, carries the load-bearing statements.

### 2. Interface-language pass + empty/edge states

Audit every account and archive surface against the charter's
quiet-affordance vocabulary ("Save to trail", "Start a trail", "Add note",
"Compare", "Why this suggestion?", "Continue this trail", "View memory",
"Pause memory"). Fix drift, SaaS-flavored copy, and hollow or missing empty
states. Assertions in existing tests pin the exact words — update them
deliberately where copy changes, never loosen them.

### 3. Accessibility pass

- Skip link to `#main` in `layouts/main`.
- Alt text on artwork images via the assistive-text chain (Twill media alt
  text → source title), and explicitly empty `alt=""` only where the image is
  decorative next to its own title.
- Keyboard/AT flow: labels on the compare checkboxes, accessible names on
  icon-only and repeated-text buttons (aria-label carrying the work's title),
  `<details>` summaries already keyboard-native, landmarks (`<main>`, `<nav>`)
  and heading order.

### 4. CuratedSet capsule + archive display

The "cheap way to make the archive feel curated pre-AI" (STRATEGY §157):
a Twill capsule — title, editorial description, browser of artworks —
rendered as a quiet strip on `/archive`. CMS nav gains a Curation section.

**FeaturedTrails is deferred**: publishing a visitor's trail requires an
explicit publishing-consent mechanism ("explicit and recorded" — STRATEGY
§156) that does not exist yet and must not be rushed into a polish phase.

### 5. Demo content + demo script

- `DemoContentSeeder` (idempotent, guarded against production): a consented
  demo user, one rich trail (items + notes) and one just-started trail over
  the imported subset, one curated set. Works against whatever subset is
  imported — selects by school/classification, fails loudly if the archive is
  empty.
- `docs/DEMO-SCRIPT.md`: the §12 smallest demo as a click-path, with the
  consent beats called out (what to show at each step and why it matters).

### 6. Performance

- Query audit on the expansion, compare, and trail-show paths; add missing
  eager loads. Verify with targeted query-count assertions where cheap.
- Confirm anonymous archive pages carry the response-cache headers and that
  no consent-gated or sessioned route ever does (test).

### 7. Smoke suite

One feature test walking the whole journey end-to-end on the fakes:
register → consent → browse → save → trail → note → expand → accept →
dismiss → compare → export → pause → resume → delete → visibly empty
dashboard. This is the eval: if it passes, the demo runs.

### 8. Runbooks + vendor-swap notes

- `docs/RUNBOOK.md`: local setup (Herd + brew Postgres + Redis), import,
  queue worker, demo seed, verification commands — all through the 1Password
  context.
- `docs/VENDOR-SWAP.md`: the contract seams (`NexusRetriever`,
  `RedisIrisMemoryContract`), what the fakes guarantee (determinism,
  citations-or-nothing, visitor-blindness, purge semantics) and therefore
  what any live client must satisfy; where bindings live; what must never
  cross each boundary.

## Deferred (stretch, explicitly out)

- `MotifTrace` + "Trace this motif" — still no surface consumes it.
- Enrichment generation behind the Twill review block — needs real AI
  plumbing decisions that belong with the vendor-swap work, not before it.
- FeaturedTrails — see task 4.

## Test plan

Methodology render + guarantees; cache-header coverage; curated set display;
demo seeder idempotence; smoke suite; existing 173 stay green (copy changes
update assertions deliberately). PHPStan level 6 clean throughout.
