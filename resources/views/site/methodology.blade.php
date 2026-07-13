@extends('layouts.main', ['page_id' => 'page-methodology'])

@section('content')
    <div class="mx-auto w-full max-w-[1600px] px-16 md:px-20 lg:px-32 xl:px-40">
        <header class="pb-32 pt-40 lg:pt-64">
            <h1 class="f-heading-01 max-w-[900px]">How memory works</h1>
            <p class="f-body mt-20 max-w-[640px]">
                This archive is built around trails, not feeds. It remembers what you
                deliberately did, explains every suggestion it makes, and lets you read,
                pause, export or erase its memory of you at any time. Everything on this
                page describes how the application actually behaves — not a policy, a
                mechanism.
            </p>
        </header>

        <div class="max-w-[720px] pb-64">
            <section class="border-dark-soft border-t py-32">
                <h2 class="f-heading-03">Memory holds explicit actions, and nothing else</h2>
                <p class="f-body mt-16">
                    Signing in turns on memory. From then on, the archive records a small,
                    fixed list of deliberate acts:
                </p>
                <ul class="f-body mt-12 flex list-disc flex-col gap-6 pl-20">
                    <li>starting, revisiting, archiving or deleting a trail</li>
                    <li>saving a work — on its own, or into a trail</li>
                    <li>writing a note</li>
                    <li>asking how works compare</li>
                    <li>dismissing a suggestion</li>
                    <li>the memory switches themselves — pausing, resuming, renewing or revoking consent, deleting</li>
                </ul>
                <p class="f-body mt-16">
                    Browsing, searching, reading and lingering are never recorded. There is
                    no background profiling, no attention tracking, and nothing you type
                    into Explore is kept beyond your current session. The list above is the
                    whole list.
                </p>
            </section>

            <section class="border-dark-soft border-t py-32">
                <h2 class="f-heading-03">The knowledge layer never learns who you are</h2>
                <p class="f-body mt-16">
                    Suggestions come from a knowledge layer that holds the collection
                    record — artists, materials, classifications, dates — and nothing about
                    any visitor. When you ask to expand a trail, it is handed only a list
                    of artwork identifiers: the works you chose, and the works to leave
                    out. No account, no name, no history crosses that boundary, so when
                    you delete your memory there is nothing on the other side to delete —
                    by design, not by cleanup.
                </p>
            </section>

            <section class="border-dark-soft border-t py-32">
                <h2 class="f-heading-03">Every suggestion explains itself</h2>
                <p class="f-body mt-16">
                    A suggestion is written down before it is shown: which work, the reason
                    in plain words, and the evidence — the fields of the collection record
                    it rests on. “Why this suggestion?” opens that stored record, not a
                    fresh answer invented after the fact. Suggestions you accept are marked
                    on the trail as accepted suggestions; a work you dismiss is never
                    suggested to you again, anywhere.
                </p>
            </section>

            <section class="border-dark-soft border-t py-32">
                <h2 class="f-heading-03">Citations over vibes</h2>
                <p class="f-body mt-16">
                    Every match in Explore, every claim in a comparison, and every
                    suggestion carries citations to the collection record — the title,
                    material, classification or date it is grounded in. Where the record is
                    silent, the interface says so instead of guessing.
                </p>
            </section>

            <section class="border-dark-soft border-t py-32">
                <h2 class="f-heading-03">Pause, export, delete — always</h2>
                <ul class="f-body mt-16 flex list-disc flex-col gap-10 pl-20">
                    <li>
                        <strong>Inspect.</strong> Your memory page shows everything held about
                        you — every event, every suggestion with its reasons, and the working
                        context the archive currently keeps — in the same form the system uses.
                    </li>
                    <li>
                        <strong>Pause.</strong> Pausing stops all memory writes and clears the
                        working context immediately. You can still read everything you built;
                        the archive just stops remembering new acts until you resume.
                    </li>
                    <li>
                        <strong>Export.</strong> The export contains the durable record and the
                        held working context — the whole memory, not a summary of it.
                    </li>
                    <li>
                        <strong>Delete.</strong> Deletion removes the durable record in one
                        transaction, purges the working context, and then reads it back to
                        verify nothing remains — the deletion fails loudly rather than
                        completing silently half-done. What remains afterwards is a minimal
                        receipt: that a deletion happened, and when.
                    </li>
                </ul>
            </section>

            <section class="border-dark-soft border-t py-32">
                <h2 class="f-heading-03">Anonymous visitors owe nothing</h2>
                <p class="f-body mt-16">
                    Without an account you can browse, search, read and explore. The only
                    thing held is a short-lived list of your recent Explore queries, tied
                    to your session, expiring on its own, and never linked to an account.
                    Signing in is the moment memory begins — that is what the account is
                    for.
                </p>
            </section>

            <p class="f-ui-01 border-dark-soft border-t pt-24 opacity-60">
                Memory agreement {{ $consentVersion }} · These statements are verified by
                the application’s own test suite.
            </p>
        </div>
    </div>
@endsection
