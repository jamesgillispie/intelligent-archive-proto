# Trails, Not Feeds

A consent-first AI-native archive prototype using NGA Open Data, Laravel, Twill, Redis Iris, and Pinecone Nexus.

## Thesis

Pinecone Nexus knows the archive. Redis Iris knows the visitor, but only with consent. Laravel/Twill owns the canonical application data.

This is a prototype for a slower, more reflective, consent-first archive experience where visitors build meaningful research trails through a cultural collection. It optimizes for understanding, not engagement.

This is NOT a chatbot project, NOT a personalized feed, and NOT a generic museum search UI.

## Foundation

You are working inside an existing Laravel/Twill starter kit from AREA 17. Treat it as the application foundation. Preserve its conventions unless there is a strong, stated reason not to. Never start from a blank Laravel app. Before proposing new patterns, check how the starter kit already solves the problem.

## Stack

- Laravel (version per starter kit; verify, do not assume)
- Twill CMS via the AREA 17 starter kit
- Tailwind CSS, Vite, Blade components
- Livewire + Flux UI for interactive components (verify availability first; the starter kit is Alpine-first and may not include either. Flux Pro is a paid package requiring composer auth credentials, which are themselves a managed secret.)
- Laravel Fortify for authentication, Laravel Socialite for optional social login
- PostgreSQL as the application database
- Redis for queues and cache; Redis Iris for consent-based visitor memory
- Pinecone Nexus for archive knowledge, semantic retrieval, and cited answers
- Laravel queues for ingestion, indexing, and AI processing
- 1Password Environments for all secrets and runtime configuration

## Vendor API rule (important)

Redis Iris and Pinecone Nexus are new products. Do NOT write concrete API calls, SDK usage, or request/response shapes for either service from memory — you will get them wrong. Instead:

- Define Laravel service interfaces (contracts) and in-memory/fake implementations.
- Keep all vendor interaction behind `app/Services/RedisIris` and `app/Services/Nexus` boundaries.
- Only implement real clients when official docs or SDK references are provided in the session.

## Secrets and environment rules

- 1Password Environments is the source of truth for secrets. `.env` files in the repo contain placeholders only.
- Never print, log, echo, commit, or include real secret values in code, docs, tests, examples, or terminal output. If you need to verify an env var exists, check for presence, not value.
- Run artisan commands, queue workers, and import jobs through the 1Password context, e.g. `op run --env-file=.env.1password -- php artisan <command>`. Use `op://vault/item/field` references in the env file.
- Environments are named `local`, `staging`, `production`. Do not assume 1Password exists in production; config keys must be populatable by platform secrets there.
- Use typed config access (`config/nga.php`, `config/nexus.php`, `config/redis_iris.php`, `config/memory.php`, `config/trails.php`). No scattered `env()` calls outside config files.
- Validate required env vars at boot or command runtime via an `EnvironmentValidationService` and fail loudly with the variable name, never the value.

## Consent model (non-negotiable)

- Login is the consent boundary for durable memory, not a generic account feature.
- Anonymous visitors get browse, search, filters, artwork detail, limited non-personalized semantic exploration, and a temporary session at most.
- Logged-in visitors get trails, saves, notes, transparent recommendations, memory inspection, export, pause, and deletion.
- The user saves first. The system infers second. The user can inspect and delete always.
- Explicit actions (saved artwork, added to trail, note written, comparison requested, trail revisited, user-tagged interest, dismissed suggestion) are the only personalization inputs. No background behavioral profiling.

## Architecture boundaries

- Three data layers: source (raw NGA records, faithful, immutable), canonical (normalized archive models, Twill-managed), visitor memory (trails, saves, notes, consent, SQL-durable with Redis Iris as contextual layer).
- Twill manages the canonical/editorial layer only, never the raw source layer.
- Distinguish source-imported fields, editorial overrides, AI-generated fields (flagged, review-required), and human-approved fields.
- Redis Iris never replaces the app database or the Nexus archive layer. SQL is the durable record; Iris is consented context.
- Service-oriented structure: `app/Services/{Nga,Import,Nexus,RedisIris,Memory,Trails}`, `app/Models/{Archive,Source,Memory}`, `app/Jobs/{Import,Indexing,Memory}`.

## Design principles

1. Trails, not feeds.
2. Consent before memory.
3. Archive first, AI second.
4. Human-readable relationships.
5. Citations over vibes. Every interpretive answer is grounded in retrieved evidence.
6. Slowness can be a feature.
7. Save explicitly, infer carefully.
8. The user can always inspect and delete memory.
9. The interface should feel cultural, not SaaS.
10. Recommendations broaden understanding, never maximize attention.
11. Secrets never belong in code, prompts, screenshots, logs, or commits.
12. Environment configuration is explicit, reproducible, and portable.

## Interface language

Quiet affordances: "Save to trail", "Start a trail", "Add note", "Compare", "Show relationships", "Why this suggestion?", "Continue this trail", "View memory", "Pause memory".

## Do not build (first pass)

Chatbot-as-primary-interface, infinite feeds, opaque tracking, automated art-historical essays, social features, public profiles, comments, likes, gamification, overbuilt dashboards, full-dataset imports before the pipeline is proven, unreviewed AI metadata, direct vendor API coupling, or anything that skips the consent model.

## Working style

- Work incrementally. Build the archive foundation before any AI integration.
- Prefer a small curated NGA subset (a few thousand records with images and rich metadata) over blind full imports.
- Write tests alongside features: import, consent, memory deletion, trail creation, authorization, config validation.
- When a phase prompt and this file conflict, ask; do not silently pick one.
