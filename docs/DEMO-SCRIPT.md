# Demo Script — the smallest demo that proves the idea

STRATEGY §12: *a logged-in visitor saves NGA artworks into a named research
trail; the app remembers it with consent; the visitor returns, clicks
"Expand this trail", and gets cited continuations and counterpoints, each of
which explains why it appeared.* Everything below runs end-to-end on the
deterministic fakes — no vendor keys, no live AI.

## Setup (once)

```sh
op run --env-file=.env.1password -- php artisan migrate
op run --env-file=.env.1password -- php artisan nga:import      # if not already imported
op run --env-file=.env.1password -- php artisan db:seed --class=DemoContentSeeder
```

The seeder creates the demo visitor **demo@trails.test** (factory password)
with one lived-in trail and one just-started trail — built through the real
services, so the ledger and item origins are genuine — plus the "Ways of
seeing weather" curated set. It refuses to run in production and skips
anything already seeded.

## The walk (10–12 minutes)

**1. Anonymous first — the tier that owes nothing.** Open `/archive` in a
private window. Point at the header line: *"nothing here is tracked or
remembered without your consent."* Browse a work; show "Related in the
collection" and open a "Why this?" — citations from the record, identical
for every visitor. Visit `/archive/explore`, search something ("storm",
"oil on panel"). Beat: **results are the same for everyone; nothing typed
here is kept beyond the session.**

**2. The consent boundary.** Click "Sign in" → "Create an account and choose
memory" and show the consent banner on the register screen — the enumerated
list, the standing rights, the agreement version, the "How memory works"
link. Beat: **login is not an account feature; it is the moment memory is
allowed to begin.** (Then sign in as demo@trails.test rather than actually
registering.)

**3. A trail is a held question.** "Your trails" → *Following …*. Show the
intent note, the items with their dates and notes, "Add note", and the item
origins. Beat: **this is a research trail, not a playlist — the visitor
wrote its purpose down.**

**4. Expand this trail — the demo's center.** Click "Expand this trail".
Walk one continuation and one counterpoint. Open "Why this suggestion?":
the trail context it was drawn from, the works dismissed and left out, and
the field-level citations. Beat: **the explanation is the stored record of
what the system did, not a story invented afterwards.** Accept one
suggestion ("Save to this trail") — it lands marked *From a suggestion you
accepted*. Dismiss another — flash: it won't be suggested again. Expand
again to prove the dismissal held.

**5. Compare.** Tick two works → "Compare selected works". Shared ground
and differences, every claim with "Grounded in" citations both ways. Beat:
**citations over vibes — where the record is silent, the page says so.**
Note the footer: asking for the comparison is itself a ledgered act.

**6. The memory is the visitor's.** "Your memory": the agreement, the
switch, saves, suggestions with their reasons, the ledger — including the
dismissal and the comparison request just made. Show "Pause memory"
(watch the trail page lose its write affordances but keep reading), resume.
Open the export: the whole record, verbatim, downloadable. Open the
deletion page: exactly what disappears, counted — including suggestions —
and read-back-verified on the other side. Beat: **inspect, pause, export,
delete — always.**

**7. Close on `/methodology`.** Every statement on it is verified by the
test suite. Beat: **not a policy — a mechanism.**

## If asked "is the AI real?"

The retrieval and memory layers run on deterministic in-process fakes behind
the same contracts a live Pinecone Nexus / Redis Iris client must satisfy
(see docs/VENDOR-SWAP.md). Swapping in live clients changes configuration,
not application code — and none of the consent guarantees above depend on
which side of that swap you are on.
