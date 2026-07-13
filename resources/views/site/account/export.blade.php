@extends('layouts.main', ['page_id' => 'page-account-export'])

@section('content')
    <div class="mx-auto w-full max-w-[900px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Your memory, portable</h1>
            <p class="f-body mt-16 max-w-[640px] opacity-80">
                The complete record: your agreements, trails, saves, notes,
                the ledger, every suggestion shown to you with its reasons and
                evidence, and a snapshot of the short-lived context currently
                held. What you can read below is exactly what the file
                contains — nothing more leaves with it.
            </p>
        </header>

        <p class="f-body mb-24">
            {{ count($export['trails']) }} {{ count($export['trails']) === 1 ? 'trail' : 'trails' }},
            {{ count($export['saves']) }} {{ count($export['saves']) === 1 ? 'save' : 'saves' }},
            {{ count($export['ledger']) }} ledger {{ count($export['ledger']) === 1 ? 'entry' : 'entries' }}.
        </p>

        <a class="f-ui-01 inline-block border border-black px-24 py-10" href="{{ route('account.export.download') }}">
            Download as JSON
        </a>

        <section class="mt-32">
            <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">The document, verbatim</h2>
            <pre class="f-ui-01 border-dark-soft overflow-x-auto border p-16">{{ $pretty }}</pre>
        </section>

        <nav class="f-ui-01 border-dark-soft mt-32 flex items-center gap-24 border-t pt-24">
            <a class="underline" href="{{ route('account.memory') }}">Back to your memory</a>
            <a class="underline" href="{{ route('account.trails') }}">Your trails</a>
        </nav>
    </div>
@endsection
