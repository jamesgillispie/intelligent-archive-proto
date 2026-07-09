# Trails, Not Feeds — Strategy

Strategic and technical scaffold for the consent-first NGA archive prototype. Produced by the Prompt 00 strategy pass (2026-07-09), grounded in an audit of this repository. Companion to the project charter (CLAUDE.md). No code was written in this pass.

---

## 1. Starter kit audit summary

### Versions and platform

| Concern | Finding | Evidence |
|---|---|---|
| Laravel | v11.48.0 (constraint `^11.0`) | `composer.lock`, `composer.json` |
| PHP | `^8.4` | `composer.json`; docker image `ghcr.io/luislavena/hydrofoil-php:8.4` in `compose.yaml` |
| Twill | 3.5.3 (constraint `^3.5.3`) | `composer.lock` |
| Database | PostgreSQL 17 (`bitnamilegacy/postgresql:17` service), `DB_CONNECTION=pgsql` | `compose.yaml`, `.env.example` |
| Runtime | Docker Compose: `app` (overmind runs `Procfile.dev`: artisan serve + `npm run dev`), `pgsql`, `mailpit`. OrbStack domains `*.laravel-twill-project.local` | `compose.yaml`, `Procfile.dev` |
| Command convention | Everything runs via `docker compose run --rm --no-deps app <command>` | `AGENTS.md` |

### Installed packages that matter here

- **Twill ecosystem**: `area17/twill`, `area17/twill-image` (responsive/lazy images), `area17/blast` (Storybook for Blade components), `area17/vitrine-ui` ^3.0 (A17's Blade component library, aliased in `vite.config.js` as `@vitrineUI`), `cwsdigital/twill-metadata` (SEO metadata trait).
- **Laravel**: `laravel/sanctum` (installed but unused by any route), `laravel/tinker`, `spatie/laravel-sitemap`, `guzzlehttp/guzzle` (HTTP client — reuse for NGA fetching).
- **Quality tooling**: Pint, php-cs-fixer, PHPStan/Larastan (`phpstan.neon`, `tools/linters.sh`, composer scripts `lint`/`format`/`phpstan`), PHPUnit ^10.5, IDE-helper with a Twill model hook (`app/Support/IdeHelper/TwillModelHook.php`).
- **Not installed**: Livewire, Flux, Fortify, Socialite, Horizon, Redis client is available via Laravel core config only (`config/database.php:142`) — there is **no Redis service in `compose.yaml`**.

### Starter kit conventions (follow these)

- **Twill Capsules, not loose modules.** Seven capsules exist under `app/Twill/Capsules/` (Pages, Homepages, Articles, ArticleTypes, AboutLandings, AboutTimelineItems, ContactLandings), each self-contained with `Models/`, `Repositories/`, `Http/{Controllers,Requests}`, `Database/migrations/`, `routes/twill.php`. Capsules are registered in `config/twill.php` (`capsules.list`) and surfaced in CMS navigation via `app/Providers/TwillServiceProvider.php`.
- **Model conventions**: extend `A17\Twill\Models\Model`, compose behaviors (`HasSlug`, `HasBlocks`, `HasMedias`, `HasRevisions`, `HasRelated`, `HasMetadata`), declare crops through `app/Support/Crops.php` constants. See `app/Twill/Capsules/Articles/Models/Article.php` as the template.
- **Front-end delivery**: plain Blade controllers (`app/Http/Controllers/ArticlesController.php` is the pattern: query scopes like `publishedInListings()`, validated filters, pagination), routes declared inside `App::booted()` in `routes/web.php` with a response-cache middleware group (`cache:public;max_age=60;s_maxage=3600`, implemented by `app/Http/Middleware/SimpleCacheHeader.php` / `CleanCacheableResponses.php`).
- **Views**: single shell layout `resources/views/layouts/main.blade.php` (skip-link, `x-navigation.header`/`footer`, modal stacks, icon sprite), page templates in `resources/views/site/`, shared Blade components in `resources/views/components/{navigation,primitives,sections,helpers,composers}`, vitrine-ui components used as `<x-vui-*>`.
- **JS**: no SPA. A17 "behaviors" pattern — small DOM-bound modules in `resources/frontend/scripts/behaviors/` auto-loaded by `@area17/a17-behaviors` (see `vite.config.js` `BEHAVIORS_PATH`). This is the sanctioned home for client-side interactivity.
- **Design tokens**: `frontend.config.json` drives `tailwind.config.cjs` through `@area17/a17-tailwind-plugins` (breakpoints, 12-col grid, spacing scale, typesets like `f-display-01`, color tokens). New UI must extend tokens here, not ad-hoc Tailwind values.
- **Admin**: Twill admin lives on a separate host (`ADMIN_APP_URL=http://admin.….test` in `.env.example`); Twill has its own `twill_users` auth, fully separate from front-end `App\Models\User`.

### Auth, queue, cache, storage state

- **Auth**: default Laravel `User` model only (`app/Models/User.php`); no registration/login routes exist on the front end at all — `routes/web.php` has zero auth routes. Fortify/Socialite are greenfield. Twill admin auth is unaffected.
- **Queue/cache/session**: `.env.example` ships `QUEUE_CONNECTION=sync`, `CACHE_DRIVER=file`, `SESSION_DRIVER=file`; config defaults are `database` for queue and cache (`config/queue.php:15`, `config/cache.php:17`) and default migrations include `jobs`/`cache` tables. Redis is configured in `config/database.php` but has no container and no env vars in `.env.example`.
- **Storage/media**: local disk default (`config/filesystems.php:15`); Twill media via Glide (`GLIDE_*` vars), S3 vars present but optional.
- **1Password**: **no setup exists.** No `.env.1password`, no `op run` references anywhere outside `node_modules`/`vendor`. The charter's secrets workflow must be created from scratch.
- **Tests**: only the two framework example tests (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`).

### Reuse / extend / avoid

- **Reuse**: capsule architecture and Article capsule as the module template; controller/route/cache-middleware pattern; `main.blade.php` shell and vitrine-ui components; behaviors JS pattern; Crops/Blocks support classes; Guzzle; linters/PHPStan/test harness; twill-image for artwork rendering; docker + overmind workflow.
- **Extend**: `compose.yaml` (add Redis service; add a queue worker process to `Procfile.dev`); `.env.example` → add NGA/Nexus/Iris/consent vars as placeholders; `config/twill.php` capsule list + `TwillServiceProvider` navigation; `frontend.config.json` tokens for the archive's visual language; `User` model (consent relationships); tests directory structure.
- **Avoid**: putting archive logic in the existing demo capsules (Articles/Pages stay as-is or get removed late); scattering `env()` calls (starter already centralizes config — keep it that way); Flux Pro (paid, composer auth secret, and it would fight vitrine-ui's design language — see §5); building on `sync` queues beyond Phase 1; touching the raw-source layer from Twill.

---

## 2. Corpus strategy

### The NGA Open Data shape

The NGA Open Data Program publishes its collection dataset as CSV files in a public GitHub repository (`NationalGalleryOfArt/opendata`), refreshed daily, released CC0. The tables below reflect the dataset's documented structure; **verify column lists against the repo's data dictionary at implementation time — treat this section as a map, not a schema.**

| File | Grain | Key fields / joins |
|---|---|---|
| `objects.csv` | one row per art object | `objectid` (PK), `accessionnum`, `title`, `displaydate`, `beginyear`/`endyear`, `medium`, `dimensions`, `creditline`, `classification`, `subclassification`, `departmentabbr`, `parentid`, `isvirtual`, `accessioned`, `inscription`, `wikidataid`, `lastdetectedmodification` |
| `constituents.csv` | one row per person/org | `constituentid` (PK), `preferreddisplayname`, `forwarddisplayname`, `nationality`, `beginyear`/`endyear`, `constituenttype`, `artistofngaobject`, `ulanid`, `wikidataid` |
| `objects_constituents.csv` | object↔constituent join | `objectid`, `constituentid`, `role`, `roletype` (artist vs donor vs owner — filter on this), `displayorder` |
| `published_images.csv` | one row per published image | `uuid`, `iiifurl`, `iiifthumburl`, `viewtype` (`primary`/alternate), `sequence`, `width`/`height`, `maxpixels`, `assistivetext`, `depictstmsobjectid` (FK → `objectid`) |
| `objects_terms.csv` | object↔term join | `termtype` (Keyword, Style, School, Theme, Technique, Place Executed…), `term` — the backbone for Theme/relationship browsing |
| `objects_text_entries.csv` | object↔text | `texttype` (provenance, exhibition history, bibliography…), `text` — the (thin) interpretive-text reservoir |
| `object_associations.csv` | object↔object | parent/child/sibling relations — seeds `RelatedArtwork` |
| Supporting | — | `objects_dimensions.csv`, `locations.csv`, `alternative_identifiers.csv`, `constituents_altnames.csv`, `constituents_text_entries.csv`, `media_items.csv`/`media_relationships.csv` |

**Rights model**: the metadata is CC0. Images are served over IIIF **only for works NGA has cleared as open-access (public domain)** — in practice, the presence of a `published_images` row is the availability signal, `maxpixels` caps derivative size for a minority of images, and `assistivetext` supplies alt text. Works still under copyright simply have no published image and must render as metadata-only cards. The importer must treat "has validated primary image" as an explicit, stored fact, not an assumption.

### Ingestion strategy

1. **Snapshot, don't stream.** Each import run downloads the CSV set at a pinned upstream git commit SHA into `storage/app/nga/{sha}/`. The SHA is recorded on a `source_imports` row. Repeatable by construction: re-running the same SHA is a no-op; a new SHA is a refresh.
2. **Fetch whole files, subset at promotion.** The CSVs are tens of megabytes — small enough to ingest completely into the source layer. The curated subset filter is applied when *promoting* source → canonical, not when fetching. This keeps the source layer faithful and lets us widen the subset later without re-downloading.
3. **Source-ID mapping**: every source row keyed by (`source_table`, `source_id`); every canonical record stores its `source_record_id` and the NGA `objectid`/`constituentid`. No canonical row exists without a traceable source row.
4. **Change detection**: a `content_hash` (stable hash of the normalized CSV row) per source record. On re-import: unchanged hash → touch `last_seen_at`; changed hash → update payload, bump `version`, emit a "changed" event for re-promotion; missing from snapshot → set `missing_from_source_at` (never hard-delete source rows). `lastdetectedmodification` from `objects.csv` is recorded but the hash is authoritative.
5. **Normalization at promotion**: promotion jobs map raw fields to canonical models (dates to integers, mediums/classifications to lookup tables, artist roles filtered to `roletype = artist`), always writing into the *source-valued* fields of canonical models (see §4 provenance) so editorial overrides survive refreshes.
6. **Partial refresh**: promotion jobs are per-record and idempotent (`UpdateOrCreate` keyed on `objectid`), so a partial refresh is just dispatching promotion for a filtered set (one object, one artist, one classification).
7. **Image validation** is its own queued step: for each primary `published_images` row in the subset, request the IIIF thumb, record HTTP status + dimensions on the canonical image record, and only then mark the artwork displayable. Failures are reported, not fatal.
8. **Queued indexing hooks**: promotion completes by firing a domain event (`ArtworkPromoted`). In Phases 1–5 nothing listens; in Phase 6 the Nexus indexing job subscribes. Ingestion never calls AI services directly.
9. **Pipeline shape**: `FetchNgaSnapshot` (command) → `ImportSourceTable` jobs (queued, chunked) → `PromoteArtwork`/`PromoteArtist` jobs → `ValidateArtworkImage` jobs → events. All under `app/Jobs/Import/`, orchestrated by an `php artisan nga:import` command, run through the 1Password context.

### Initial curated subset — recommendation

**Accessioned, non-virtual paintings with a validated primary published image** (`classification = 'Painting'`, `accessioned`, not `isvirtual`, joined to a `viewtype = 'primary'` image), which is roughly the NGA's ~4,000 paintings, with **American school prioritized for the first demo trail content**.

Tradeoffs considered:

- **Full dataset (~130k+ objects)** — rejected for pass one: dominated by works on paper without images, unproven pipeline at scale, and it violates the charter's "prove the pipeline first" rule. The source layer design ensures widening later is cheap.
- **Prints/drawings subset** — largest department but image coverage and per-object metadata richness are weaker, and trails through 90k prints are hard to curate for a demo.
- **Sculpture/decorative arts** — too few records for interesting semantic neighborhoods.
- **Random mixed sample** — statistically representative but experientially incoherent; trails need dense neighborhoods (Hudson River School, Gilded Age portraiture, Impressionism) to feel meaningful.

Paintings win on image coverage, metadata density, term richness (styles/schools/themes), and demo legibility, while staying in the "few thousand records" budget.

---

## 3. Data model

Three layers, three namespaces, three concerns. Field-level specs land in the phases that build each model; this is the structural contract.

### Source layer — `app/Models/Source` (plain Eloquent, no Twill, append-preserving)

Concern: **fidelity and provenance of the data itself.** One physical `source_records` table with a JSONB payload keeps the layer schema-proof against upstream CSV changes; thin typed models scope it per table.

| Model | Purpose | Key fields | Relationships | Layer concerns |
|---|---|---|---|---|
| `SourceImport` | One import run | upstream commit SHA, snapshot path, per-table counts, status, started/finished, report JSON | hasMany SourceRecord | The provenance anchor for everything downstream |
| `SourceRecord` | One raw upstream row, verbatim | `source_table`, `source_id`, `payload` (JSONB), `content_hash`, `version`, `first_seen_at`, `last_seen_at`, `missing_from_source_at`, `source_import_id` | belongsTo SourceImport | Never edited by hand, never Twill-managed, never deleted on upstream removal |
| `SourceArtwork` / `SourceArtist` / `SourceImage` / `SourceTerm` / `SourceTextEntry` | Typed scopes over `source_records` (single-table, discriminated by `source_table`) with payload accessors | as SourceRecord | — | Ergonomics only; no extra tables unless query performance forces it |

### Canonical layer — Twill capsules under `app/Twill/Capsules` (see §4)

Concern: **Twill module status and public display.**

| Model | Purpose | Key fields | Relationships | Layer concerns |
|---|---|---|---|---|
| `Artwork` | The public artwork record | source-valued fields (title, dates, medium text, credit line, inscription…), editorial overrides, slug, `is_displayable`, `nga_object_id`, `source_record_id` | belongsToMany Artist (role-qualified); belongsTo Classification, Medium, Department; belongsToMany Theme; hasMany ImageAsset; RelatedArtwork self-relation | Field provenance (§4); revisions; only displayable when image validated or explicitly metadata-only |
| `Artist` | Constituent promoted as artist | display name, nationality, life dates, ULAN/Wikidata ids, `nga_constituent_id` | belongsToMany Artwork | Imported-only in pass one; biography is an editorial/AI-flagged field later |
| `Classification` / `Medium` / `Department` | Lookup vocabularies from object fields | name, slug, source string | hasMany Artwork | Imported-only; merged/aliased editorially if needed |
| `Theme` | Curated vocabulary seeded from `objects_terms` (Theme/Style/Keyword) | name, slug, `term_type`, editorial description | belongsToMany Artwork | The one vocabulary editors actively curate |
| `ImageAsset` | A published IIIF image | IIIF URLs, dimensions, `viewtype`, `maxpixels`, `assistivetext`, validation status/checked-at | belongsTo Artwork | Rights signal lives here; never hotlink-unvalidated |
| `RelatedArtwork` | Explicit object↔object relation | relation type (source-association vs editorial vs approved-AI), qualifier text | Artwork↔Artwork pivot | Human-readable relationship labels (charter principle 4) |
| `ArtworkEnrichment` | AI-generated field values awaiting review | artwork id, field, generated text, model/prompt ref, status (`pending`/`approved`/`rejected`), reviewer, citations JSON | belongsTo Artwork | AI text never enters display fields until approved (§4) |

### Memory layer — `app/Models/Memory` (plain Eloquent; SQL is the durable record)

Concern: **consent requirement, SQL vs Iris placement, deletion and export behavior.** Everything below is SQL. Redis Iris only ever holds *derived, rebuildable* context (§7); deleting a user's memory = SQL delete + Iris namespace purge.

| Model | Purpose | Key fields | Relationships | Consent / deletion / export |
|---|---|---|---|---|
| `MemoryConsent` | The consent record login creates | user id, consent text version, `granted_at`, `paused_at`, `revoked_at` | belongsTo User | Authoritative gate every memory write checks; export includes it; revocation triggers deletion flow |
| `ResearchTrail` | A named research trail | user id, title, intent note, status (active/archived) | hasMany items; belongsTo User | Consent-required; exportable as structured JSON/markdown; deletable individually |
| `ResearchTrailItem` | One artwork in a trail | trail id, artwork id, position, added_at, origin (`user_saved` / `suggestion_accepted`), note | belongsTo Trail, Artwork | Origin field keeps AI influence inspectable |
| `ArtworkSave` | A save outside/before any trail | user id, artwork id, saved_at | belongsTo User, Artwork | The lightest explicit signal; consent-required |
| `UserNote` | Freeform note on artwork or trail | user id, notable (morph), body | morphTo | Private, exportable, deletable; never an AI input without consent flag |
| `Recommendation` | One suggestion shown to a user | user id, trail id, artwork id, reason text, citations JSON, source query ref, status (`shown`/`accepted`/`dismissed`) | belongsTo Trail, Artwork | Stored so "Why this suggestion?" is answerable after the fact; dismissals are memory too |
| `RecommendationFeedback` | Explicit reaction | recommendation id, action, created_at | belongsTo Recommendation | One of the enumerated personalization inputs |
| `MemoryEvent` | Append-only ledger of every explicit action (save, trail add, note, compare, revisit, dismiss, tag) | user id, event type, subject morph, payload, occurred_at | belongsTo User | **The only personalization input stream**; fully user-visible in the Memory Dashboard; export = this ledger + the tables above; delete = truncate per user |
| `PersonalizationSetting` | User's memory switches | user id, memory_paused, semantic_suggestions_enabled | belongsTo User | Pause ≠ delete: pause stops writes and Iris reads, keeps SQL |

---

## 4. Twill architecture

Twill manages the canonical layer only. Recommended module set, all as capsules following the Articles anatomy:

| Capsule | Purpose | Editability class | Notes |
|---|---|---|---|
| `Artworks` | Core record; CMS is the review/override surface, not the authoring surface | **Editorially enriched**: imported base + override fields + approved AI fields | Revisions on; no create button in CMS (records come from import); dashboard module |
| `Artists` | Constituent display | **Imported-only** pass one; biography becomes enriched later | No CMS create |
| `Departments`, `Classifications`, `Mediums` | Lookup vocabularies | **Imported-only** | Browse/merge only |
| `Themes` | Curated subject/style vocabulary | **Editable**: seeded from terms, curated by editors | The main editorial lever on the archive graph |
| `CollectionPages` / `EditorialPages` | Contextual editorial content | **Editable** (pure Twill: blocks, medias) | Extend/reuse the existing `Pages` capsule pattern rather than inventing a new one — possibly literally the `Pages` capsule with new blocks |
| `FeaturedTrails` | Editorially highlighted public trails | **Editable**; references a `ResearchTrail` snapshot, never live user memory | Publishing consent from the trail owner is explicit and recorded |
| `CuratedSets` | Hand-picked artwork groupings (demo seeds, study sets) | **Editable** | Cheap way to make the archive feel curated pre-AI |
| `ImportBatches` | Read-only CMS window onto `source_imports` | **Imported-only**, view-only | Editors see counts/diffs/failures; no mutation |

### Field-provenance pattern (imported vs editorial vs AI vs approved)

Applies to `Artwork` (and later `Artist`):

- **Source-valued columns** (`source_title`, `source_display_date`, …) are written *only* by promotion jobs. Refreshes update these freely.
- **Override columns** (`title_override`, …) are written *only* through Twill forms. Display accessors resolve `override ?? source`. A refresh can never clobber editorial work, and clearing an override transparently reverts to source.
- **AI-generated values** live in `ArtworkEnrichment` rows (`status = pending`), never in display columns. The Twill Artwork form shows pending enrichments in a review block; **approving** copies the value into the corresponding override column and stamps the enrichment `approved` with reviewer identity — so "human-approved" is literally "an editorial override with an approval trail".
- The form UI labels each field group (Imported / Editorial / AI suggestions awaiting review), giving editors the provenance distinctions the charter requires without a per-field metadata table in pass one.

CMS navigation (`TwillServiceProvider::navigation()`) gains an "Archive" section (Artworks, Artists, vocabularies), a "Curation" section (Themes, CuratedSets, FeaturedTrails, editorial pages), and an "Imports" section.

---

## 5. Frontend IA

### Interactivity decision (from the audit)

Livewire and Flux are **not installed**; the starter kit is Blade + Alpine-style behaviors + vitrine-ui. Recommendation:

- **Skip Flux Pro entirely** for the prototype: it's paid, needs composer auth secrets, and duplicates what vitrine-ui + `frontend.config.json` tokens already provide. It would also push the UI toward SaaS idioms the charter rejects.
- **Stay Blade + behaviors for all read-only surfaces** (grid, cards, detail, filters) — the starter's native pattern.
- **Add `livewire/livewire` ^3 (free) in Phase 4** for the handful of stateful, consent-gated components where server round-trips carry the interaction (saving, trail picking, memory dashboard, compare, explore panel). Livewire components are styled with existing tokens/vitrine-ui, no new design system.

### Route map

All front routes join the existing cached/`web` groups in `routes/web.php`; memory routes use `auth` + a `consented` middleware and are never response-cached.

| Route | Anonymous | Logged-in (consented) | Iris | Nexus |
|---|---|---|---|---|
| `/archive` | Browse grid, filters (classification, theme, artist, date), keyword search | + save affordances on cards | — | — |
| `/archive/artworks/{slug}` | Full detail: image, metadata, provenance text, human-readable relationships | + "Save to trail", "Add note", per-user context ("in your trail X") | read active-trail context | related-works panel (non-personalized for anon) |
| `/archive/artists/{slug}` | Artist page, their works | + save affordances | — | — |
| `/archive/explore` | Limited non-personalized semantic exploration (rate-limited, sessioned) | Full semantic explore seeded by user's trails | anon: `session:{id}` temp context; auth: user context | primary Nexus surface |
| `/archive/trails/{trail}` | Only if trail is explicitly published (FeaturedTrail) | Owner: full trail workspace — timeline, notes, expand, compare | trail summary context | "Expand this trail" |
| `/archive/compare` | — (login prompt explaining why) | Side-by-side of saved works + grounded comparison | reads selection context | comparison query |
| `/account/memory` | — | Memory dashboard: consent state, event ledger, Iris-derived context inspection, pause, delete | read (display) + purge | — |
| `/account/trails` | — | Trail list, create, archive | — | — |
| `/account/export` | — | Full memory export (JSON + human-readable) | exported context snapshot | — |
| `/methodology` | Static editorial page: how memory, consent, citations work | same | — | — |
| `/login`, `/register`, `/logout` | Fortify routes; registration screen *is* the consent explanation | — | — | — |

### Component plan

| Component | Tech | Notes |
|---|---|---|
| `ArtworkGrid`, `ArtworkCard` | Blade + twill-image | Card shows provenance-aware fields; save button slot when consented |
| `ArchiveSearch` (keyword + filters) | Blade form + behavior | Follows `ArticlesController` validated-filter pattern; no JS framework needed |
| `SemanticExplorePanel` | Livewire | Debounced intent input, streamed result sets with citations; anon variant is rate-limited and non-personalized |
| `SaveToTrailButton` + `TrailPicker` | Livewire | The core consent-gated write; optimistic UI, records `MemoryEvent` |
| `TrailTimeline` | Blade (+ behavior for reorder) | Trail as a readable narrative sequence, not a feed |
| `CompareWorksPanel` | Livewire | Two+ saved works, grounded comparison with `SourceCitationPanel` |
| `MemoryConsentBanner` | Blade | Shown at login/registration; states exactly what will be remembered |
| `MemoryDashboard` | Livewire | Event ledger, Iris context inspection ("what the system currently holds"), pause/delete controls |
| `RecommendationExplanation` ("Why this suggestion?") | Blade partial | Renders `Recommendation.reason` + citations; every suggestion ships with one |
| `SourceCitationPanel` | Blade partial | Uniform citation rendering for any Nexus-grounded text |
| `TrailCard`, `StartTrailPrompt` | Blade | Quiet affordances per charter language |

---

## 6. Nexus strategy (interface-level only)

Per the charter's vendor rule: **contracts and fakes only** — no API shapes from memory. Everything lives behind `app/Services/Nexus`.

**What gets indexed** (one "artwork document" per displayable canonical artwork, built from canonical — never raw — data):

1. Structured metadata: title, artist(s), dates, classification, medium, department, themes/terms.
2. Textual material: provenance/exhibition texts from source, editorial descriptions, **approved** enrichments only. AI-pending text is never indexed.
3. Relationship edges: artist-shared, theme-shared, source associations, editorial relations — so retrieval can explain itself in human-readable terms.

Each indexed unit carries its `artwork_id`, `nga_object_id`, and per-passage identifiers so any retrieved evidence can be cited back to a specific field or text entry.

**Contracts** (in `app/Services/Nexus/Contracts`):

- `NexusIndexer` — `upsertArtwork(ArtworkDocument $doc)`, `removeArtwork(int $artworkId)`; called only by queued jobs in `app/Jobs/Indexing`.
- `NexusRetriever` — intent-shaped methods returning typed DTOs, not raw hits.

**Artifact/DTO shapes** (plain PHP DTOs in `app/Services/Nexus/Artifacts`):

- `ArtworkContext` — the grounded context bundle for one artwork (fields + passages + edge list, each with citation ids).
- `RelatedArtworkSet` — ranked related works, each with relation kind + human-readable reason + `CitationBundle`.
- `TrailExpansion` — candidate additions for a trail: continuations and deliberate counterpoints, each explained and cited.
- `ArtworkComparison` — grounded similarities/differences between 2+ works, per-claim citations.
- `MotifTrace` — a motif/theme traced across works and time, as an ordered, cited path.
- `CitationBundle` / `Citation` — evidence references (artwork id, field or text-entry id, quoted span). **No DTO carrying interpretive text exists without one.**

**Example query intents**: "find works that extend this trail", "find surprising counterpoints to this trail", "explain why these works belong together, with citations", "trace this motif across the collection", "what in the metadata connects these two paintings?".

**Fake implementation** (`app/Services/Nexus/Fake/FakeNexus`): deterministic, in-memory/SQL-backed — relatedness from shared artist/theme/period/medium overlap, "citations" pointing at the actual canonical fields that overlap. This makes every combined flow (§8) demoable and testable before any vendor call, and defines the quality bar the real client must meet. Binding switches on `config/nexus.php` (`driver: fake|live`).

---

## 7. Redis Iris strategy (interface-level only)

Same rule: contract + fake behind `app/Services/RedisIris`; config in `config/redis_iris.php`. Iris is **consented context, derived and rebuildable — never the system of record.** Every namespace below must be reconstructible from SQL, inspectable in the Memory Dashboard, and purgeable in one operation.

| Namespace | Purpose | Source data | Consent | TTL / durability | SQL mirror | Deletion / export | Privacy risk |
|---|---|---|---|---|---|---|---|
| `user:{id}:active_trail` | "Continue where I left off" pointer + working state | Trail id, last items, last activity | **Required** | Days–weeks TTL, refreshed on use | Derivable from `ResearchTrail`/`MemoryEvent` | Purged on delete/pause; export shows current value | Low — but reveals research focus; never expose cross-user |
| `user:{id}:recommendation_context` | Compact context for suggestion queries (trail themes, tagged interests, dismissals) | Explicit `MemoryEvent`s only | **Required** | Short TTL, rebuilt on demand | Fully derived | Purged on delete; rebuilt only while consent active | **Highest** — this *is* the personalization profile; contents shown verbatim in dashboard |
| `trail:{id}:semantic_summary` | Cached summary/theme-set of a trail for expansion queries | Trail items + canonical metadata (+ Nexus artifacts) | Required (owner's) | Until trail changes; content-hash keyed | Derived from trail | Deleted with trail; included in export | Medium — trail content, keyed by trail not user |
| `session:{id}:anonymous_context` | Temporary explore context for anonymous visitors | Current-session explore inputs only | Not required — but strictly session-scoped, **no identity linkage** | Minutes–hours TTL, hard cap | None (ephemeral by design) | Expires; never migrated to a user account without explicit consent at login | Must never accumulate; this is the line between session state and profiling |
| `user:{id}:memory_flags` | Fast-path consent/pause flags for request-time checks | `MemoryConsent`/`PersonalizationSetting` | n/a (it enforces consent) | Cache semantics; SQL authoritative | Yes — pure cache | Invalidated on any consent change | Low |

**Service layer** (in `app/Services/Memory` and `app/Services/RedisIris`):

- `ConsentService` — the single authority: grant/pause/revoke, request-time checks; SQL-backed, Iris-cached. Every Iris write path calls it first.
- `VisitorContextService` — resolves "who is this and what may we remember" → anonymous session context vs consented user context.
- `TrailMemoryService` — maintains `active_trail` and `trail:{id}:semantic_summary` in response to explicit trail events.
- `RecommendationContextService` — builds/reads `recommendation_context` from `MemoryEvent`s; the only component allowed to assemble personalization input.
- `MemoryExportService` — SQL memory tables + a live snapshot of the user's Iris namespaces → JSON + human-readable export.
- `MemoryDeletionService` — transactional: SQL deletes, then Iris namespace purge, then verification read-back; emits a final `MemoryEvent`-style audit record of the deletion itself (kept minimal: "deletion occurred at T").

`IrisClient` contract + `FakeIris` (array/Redis-hash-backed) ship first; the real client only when official docs are in session.

---

## 8. Combined flows

**A. Save artwork to trail** — User clicks "Save to trail" → `TrailPicker` (Livewire) → `ConsentService` check → SQL: `ResearchTrailItem` insert + `MemoryEvent(artwork_saved_to_trail)` in one transaction → Iris: `TrailMemoryService` refreshes `user:{id}:active_trail` and invalidates `trail:{id}:semantic_summary` → UI: quiet confirmation ("Saved to *Dutch Interiors*"), card state updates → no Nexus call; saving is cheap and AI-free.

**B. Continue where I left off** — Login → dashboard/`/account/trails` asks Iris for `active_trail` → hit: render "Continue this trail" with last-seen items (SQL-verified — Iris supplies the pointer, SQL supplies truth) → miss/expired: rebuild from most recent `MemoryEvent`s → UI links into the trail workspace → viewing emits `MemoryEvent(trail_revisited)`, which refreshes the Iris pointer.

**C. Expand this trail** — Owner clicks "Expand this trail" → Iris: `trail:{id}:semantic_summary` (rebuilt via `TrailMemoryService` if stale) + `recommendation_context` (for dismissal-avoidance only) → Nexus: `NexusRetriever::trailExpansion()` with trail summary → returns `TrailExpansion` with cited continuations *and counterpoints* (charter: broaden, don't narrow) → SQL: each shown suggestion persisted as `Recommendation(shown)` → UI: suggestion panel, every card carrying `RecommendationExplanation` + `SourceCitationPanel` → feedback: accept → `ResearchTrailItem(origin=suggestion_accepted)` + event; dismiss → `Recommendation(dismissed)` + event → both update `recommendation_context`.

**D. Compare saved works** — User selects 2+ saved works → `/archive/compare` (consented) → SQL: resolve saves; `MemoryEvent(comparison_requested)` → Nexus: `NexusRetriever::compare()` → `ArtworkComparison` with per-claim citations → UI: side-by-side images (twill-image), grounded similarities/differences, citations expandable → "Add both to trail" affordance closes the loop back to flow A. Iris: selection context only (short-lived).

**E. Why this suggestion?** — User clicks "Why this suggestion?" on any recommendation → SQL: load the stored `Recommendation` (reason + citations + originating query ref — no fresh AI call; the answer is what the system *actually did*) → Iris: only to display "context used at the time" from the stored payload → UI: plain-language explanation — which trail items and which explicit actions led here, which retrieved evidence supports it → feedback: "not helpful" → `RecommendationFeedback` + event.

**F. Delete memory** — `/account/memory` → user chooses scope (one trail / everything) → confirm with plain-language statement of exactly what disappears → SQL: transactional delete of memory tables in scope → Iris: `MemoryDeletionService` purges affected namespaces, then read-back verification → Nexus: nothing to delete by design (no per-user data ever indexed) — recorded in the methodology page as a guarantee → UI: confirmation with post-deletion dashboard state (visibly empty) → minimal deletion receipt retained.

---

## 9. Architecture comparison and risks

### Comparison

| Architecture | What it gives | What it lacks | Verdict |
|---|---|---|---|
| Plain RAG (embed + retrieve + LLM answer) | Cheapest path to "related works" and grounded text | No visitor context, no consent semantics, citation discipline hand-rolled, everything bespoke | The fallback if Nexus disappoints — the `NexusRetriever` contract is deliberately implementable this way |
| Pinecone Nexus alone | Archive knowledge, retrieval, cited answers | Knows nothing of the visitor; "expand *my* trail" needs user context injected each call | Right tool for the archive half only |
| Redis Iris alone | Consented visitor context, session/user memory | No archive knowledge — nothing to recommend *from* | Right tool for the visitor half only |
| **Combined (recommended)** | Clean split matching the charter: Nexus knows the archive, Iris knows the visitor (with consent), SQL owns truth; each layer independently fake-able, testable, replaceable | Two immature vendor APIs, more moving parts, config surface | Correct **because** the boundaries are contracts — the combination is an architecture, not a coupling |

### Risks and de-risking

| Risk | De-risk |
|---|---|
| Overengineering the prototype | Roadmap phases 1–4 ship a complete, AI-free archive+trails product; DTO/table specs stay minimal until their phase |
| Immature vendor APIs (Iris, Nexus) | Charter's vendor rule: contracts + deterministic fakes; the demo must run end-to-end on fakes; real clients are a swap, not a rewrite |
| Vendor fit turns out wrong | `NexusRetriever` is implementable as plain RAG; `IrisClient` as vanilla Redis structures — exits are designed in |
| Privacy complexity | One `ConsentService` gate on every memory write; SQL-authoritative; Iris derived+purgeable; dashboard shows raw held context; deletion is transactional with read-back verification and tests |
| Cost | Few-thousand-record corpus; indexing only displayable artworks; Iris namespaces small and TTL'd; fakes cost nothing in dev/CI |
| Too much AI too early | Phases 1–4 contain zero AI by construction; `ArtworkPromoted` events exist but nothing listens until Phase 6 |
| Thin NGA interpretive text | Acknowledge it: lean on structured metadata + terms for relatedness; treat `objects_text_entries` as bonus; editorial descriptions and *reviewed* enrichments backfill the gap deliberately |
| Unreviewed AI metadata leaking | Structural: AI output lives only in `ArtworkEnrichment(pending)`; display reads overrides+source; indexer skips non-approved text — leak requires actively bypassing three layers |
| Config sprawl / secrets drift | Five typed config files (`nga`, `nexus`, `redis_iris`, `memory`, `trails`); `EnvironmentValidationService` failing loudly by name; `.env` placeholders only; 1Password run-context per charter |

---

## 10. Feature slate

| Feature | User state | Goal | Interaction | Data touched | Privacy notes | Difficulty | Prototype value |
|---|---|---|---|---|---|---|---|
| Start a Trail | Logged-in, consented | Begin a named research thread | "Start a trail" from any artwork or `/account/trails`; name + optional intent note | SQL (Trail, Event); Iris `active_trail` | Explicit act; the moment memory visibly begins | Low | **Core** — the product thesis in one verb |
| Save to Trail | Logged-in | Collect a work into a trail | `SaveToTrailButton` + `TrailPicker` on cards/detail | SQL (TrailItem, Save, Event); Iris refresh | The canonical explicit signal | Low | **Core** |
| Continue This Trail | Returning, consented | Resume without a feed | Quiet "Continue" panel on return | Iris `active_trail` → SQL verify | Memory as service, not surveillance — pointer only | Low–Med | High — makes memory *felt* |
| Expand This Trail | Logged-in, trail ≥ ~3 items | Grow the trail with grounded suggestions | "Expand this trail" in workspace | Iris (summary, rec context) + Nexus `TrailExpansion` + SQL Recommendations | Suggestions logged + explainable; dismissals respected | High | **The demo centerpiece** |
| Find a Counterpoint | Logged-in | Broaden, not narrow | "Find a counterpoint" on trail/artwork | Same as Expand, counterpoint intent | Anti-filter-bubble by design (charter principle 10) | Med–High | High — differentiator vs recommender clichés |
| Compare These Works | Logged-in | Grounded side-by-side understanding | Select 2+ saved works → compare panel | SQL saves + Nexus `ArtworkComparison` (+ Event) | Comparison request is itself an explicit signal | Med | High — shows citations beating vibes |
| Why This Work? | Any suggestion recipient | Full recommendation transparency | "Why this suggestion?" on every card | SQL stored Recommendation (+ stored Iris context snapshot) | Explanation from what actually happened, not post-hoc | Low (given C) | High — trust made concrete |
| Trace a Motif | Logged-in (limited anon variant) | Follow a theme across the collection | From a Theme/term: "Trace this motif" | Nexus `MotifTrace` + canonical | Non-personalized variant safe for anon | Med–High | Medium — stretch if time allows |
| Build a Study Set | Logged-in | Purposeful export artifact | Assemble from trail → annotated set → export | SQL (trail, notes) + `MemoryExportService` | Export is a first-class right, not a settings burial | Med | Medium — proves data portability |
| Memory Dashboard | Logged-in | Inspect, pause, delete everything | `/account/memory` | SQL memory tables + Iris inspection/purge | **The consent model's proof**; shows Iris contents verbatim | Med | **Core** — non-negotiable per charter |

---

## 11. Roadmap

Phases 1–4 involve no AI. Each phase lands with its tests; `composer lint` + `phpstan` clean throughout.

**Phase 0 — Audit and scope (this document).** Goals: audit, strategy, decisions. Files: `docs/STRATEGY.md`; commit charter as `CLAUDE.md`. Success: strategy approved; smallest-demo definition agreed.

**Phase 1 — Canonical archive model + Twill modules.** Goals: Artworks/Artists/vocabulary capsules with provenance-aware fields, CMS navigation, seed factories. Files: `app/Twill/Capsules/{Artworks,Artists,Classifications,Mediums,Departments,Themes}`, `config/twill.php`, `TwillServiceProvider`, capsule migrations, factories. Packages: none new. Tests: model relations, provenance accessor (`override ?? source`), Twill module smoke tests. Risks: over-speccing fields — keep to fields the archive pages need. Success: CMS shows empty archive modules; factories can fill a believable grid.

**Phase 2 — NGA ingestion pipeline.** Goals: snapshot fetch, source layer, promotion, image validation, curated-subset import proven end-to-end. Files: `app/Services/{Nga,Import}`, `app/Jobs/Import/*`, `app/Models/Source/*`, `config/nga.php`, `nga:import` command, source migrations; `EnvironmentValidationService` + `.env.1password` + `.env.example` additions; Redis service in `compose.yaml` + queue worker in `Procfile.dev` (queues move to Redis here). Tests: fixture-CSV import, idempotent re-import, change detection, subset filter, image-validation handling, config validation. Risks: CSV drift (pin SHA; assert headers), image-endpoint throttling (rate-limit + backoff). Success: `op run … php artisan nga:import` populates ~4k paintings browsable at `/archive` (basic grid from Phase 1 templates).

**Phase 3 — Fortify/Socialite + consent boundary.** Goals: front-end auth where registration *is* consent; `MemoryConsent`, `PersonalizationSetting`, `ConsentService`, `consented` middleware, methodology page. Files: Fortify installation + views styled to starter conventions, `app/Services/Memory/ConsentService.php`, `app/Models/Memory/*` (consent tables), `config/memory.php`. Packages: `laravel/fortify` (+ `laravel/socialite` optional, may defer). Tests: consent grant/pause/revoke, middleware gating, anonymous limits. Risks: Fortify view scaffolding fighting starter layout — build views by hand into `layouts/main`. Success: login/registration flows state memory terms plainly; consent state inspectable.

**Phase 4 — Research trails.** Goals: trails, saves, notes, `MemoryEvent` ledger, trail workspace UI, Livewire introduced. Files: memory models + migrations, `app/Services/Trails`, controllers/routes (`/archive/trails/*`, `/account/trails`), `SaveToTrailButton`, `TrailPicker`, `TrailTimeline`, `MemoryDashboard` (SQL-only version), `config/trails.php`. Packages: `livewire/livewire` ^3. Tests: trail CRUD + authorization (owner-only), event ledger writes, save flows, dashboard deletion of individual items. Risks: Livewire vs behaviors friction — confine Livewire to the listed components. Success: **flows A and (SQL-only) B work**; a user can build, revisit, inspect, and delete trails with zero AI.

**Phase 5 — Redis Iris memory layer.** Goals: `IrisClient` contract + `FakeIris`, namespaces, context services, full deletion/export, dashboard shows Iris context. Files: `app/Services/RedisIris/*`, `app/Services/Memory/{VisitorContext,TrailMemory,RecommendationContext,MemoryExport,MemoryDeletion}Service.php`, `config/redis_iris.php`, `app/Jobs/Memory/*`. Tests: consent-gated writes, TTL/rebuild logic, purge-with-verification, export completeness, anonymous session isolation. Risks: fake diverging from eventual real semantics — keep the contract minimal (get/set/purge/inspect per namespace). Success: flow B via Iris; flow F end-to-end; dashboard shows and purges held context.

**Phase 6 — Nexus archive layer.** Goals: `NexusIndexer`/`NexusRetriever` contracts, DTOs, `FakeNexus`, queued indexing off `ArtworkPromoted`, non-personalized `/archive/explore` + related-works panel. Files: `app/Services/Nexus/*`, `app/Jobs/Indexing/*`, `config/nexus.php`, explore UI. Tests: document building excludes unapproved text, indexing idempotence, fake retrieval determinism, citation presence enforced on every DTO. Risks: no real vendor docs yet — that's fine; fakes carry the demo. Success: related works with human-readable, cited reasons on artwork pages; explore works anonymously within limits.

**Phase 7 — Combined flows.** Goals: flows C, D, E; Recommendation persistence; counterpoints; feedback loop. Files: recommendation models/migrations, `CompareWorksPanel`, `SemanticExplorePanel` (personalized), `RecommendationExplanation`, expansion orchestration in `app/Services/Trails`. Tests: expand-trail end-to-end on fakes, dismissal suppression, "why this suggestion" renders stored evidence, compare grounding. Risks: orchestration sprawl — one service method per flow. Success: **the smallest demo (§12) runs end-to-end**, on fakes or live clients interchangeably.

**Phase 8 — Polish, eval, docs.** Goals: interface-language pass (quiet affordances), methodology page completion, seed curated demo content (CuratedSets, FeaturedTrails), performance (response cache for anon archive pages), accessibility pass (skip links, `assistivetext` alt text), `docs/` for runbooks, real-vendor swap notes. Tests: smoke suite across all flows, a11y checks. Success: demoable prototype with honest docs.

---

## 12. Closing summary

**Product thesis.** A consent-first research companion for a cultural collection: visitors build durable, inspectable research trails through NGA artworks; the system remembers only what they explicitly did, explains every suggestion with citations, and broadens rather than captures attention. Trails, not feeds.

**First corpus subset.** Accessioned, non-virtual NGA paintings with validated primary open-access images (~4k works), American school prioritized for demo trails.

**Recommended architecture.** Laravel 11 + Twill 3.5 capsules on the AREA 17 starter kit; three data layers (immutable JSONB source records → provenance-aware canonical capsules → SQL-durable memory models); Redis Iris as derived consented context and Pinecone Nexus as the archive knowledge layer, both behind contracts with deterministic fakes; Livewire 3 only for consent-gated stateful components; no Flux.

**1Password strategy.** `.env.1password` with `op://vault/item/field` refs per environment (`local`/`staging`/`production` items); all artisan/queue/import commands via `op run --env-file=.env.1password --`; repo `.env`/`.env.example` hold placeholders only; production populated by platform secrets (no `op` dependency); five typed config files; `EnvironmentValidationService` fails loudly by variable name at boot/command-start, never printing values.

**First 10 implementation tasks.**
1. Commit the charter as `CLAUDE.md`; commit this strategy.
2. Add Redis service to `compose.yaml`, queue worker to `Procfile.dev`.
3. Create `.env.1password` scaffold + placeholder additions to `.env.example`.
4. Build `EnvironmentValidationService` + `config/{nga,memory,trails}.php` skeletons with tests.
5. Generate `Artworks` capsule (models, migration, repository, Twill form) with provenance field pattern.
6. Generate `Artists` + vocabulary capsules; wire CMS navigation.
7. Source-layer migrations + `SourceImport`/`SourceRecord` models with hash logic + tests.
8. `FetchNgaSnapshot` command + `app/Services/Nga` CSV reader against fixture files.
9. Promotion jobs (`PromoteArtwork`, `PromoteArtist`) + subset filter + idempotence tests.
10. `ValidateArtworkImage` job + `/archive` grid rendering the imported subset.

**First 5 files to inspect (for whoever implements Phase 1).** `app/Twill/Capsules/Articles/Models/Article.php` (model conventions), `config/twill.php` (capsule + dashboard registration), `app/Providers/TwillServiceProvider.php` (navigation), `app/Http/Controllers/ArticlesController.php` (front controller pattern), `resources/views/layouts/main.blade.php` (page shell).

**First 5 Twill modules.** Artworks, Artists, Classifications, Themes, ImportBatches.

**First 5 components.** ArtworkCard, ArtworkGrid, ArchiveSearch, SaveToTrailButton (+TrailPicker), TrailTimeline.

**First Iris integration point.** `user:{id}:active_trail` behind `TrailMemoryService` — smallest namespace, powers "Continue this trail", exercises the full consent → write → inspect → purge lifecycle.

**First Nexus integration point.** `NexusRetriever::relatedTo()` feeding the non-personalized related-works panel on artwork detail pages — no user data involved, citations required from day one.

**Smallest demo that proves the idea.** A logged-in visitor saves several NGA artworks into a named research trail. The app remembers the trail with consent. The visitor returns and clicks "Expand this trail." Redis Iris provides trail/user context, Pinecone Nexus returns related or counterpoint artworks with citations, and the interface explains why each suggestion appeared. (Runs end-to-end on `FakeIris`/`FakeNexus` at Phase 7; swapping in live clients changes no application code.)
