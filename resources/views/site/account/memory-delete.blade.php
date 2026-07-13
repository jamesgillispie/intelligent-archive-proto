@extends('layouts.main', ['page_id' => 'page-account-memory-delete'])

@section('content')
    <div class="mx-auto w-full max-w-[640px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Delete everything?</h1>
        </header>

        <p class="f-body max-w-[560px]">
            This removes, permanently and immediately:
        </p>

        <ul class="f-body mt-16 flex max-w-[560px] list-disc flex-col gap-4 pl-20">
            <li>{{ $counts['trails'] }} {{ $counts['trails'] === 1 ? 'trail' : 'trails' }},
                holding {{ $counts['works'] }} saved {{ $counts['works'] === 1 ? 'work' : 'works' }}</li>
            <li>{{ $counts['saves'] }} {{ $counts['saves'] === 1 ? 'save' : 'saves' }} outside trails</li>
            <li>{{ $counts['notes'] }} {{ $counts['notes'] === 1 ? 'note' : 'notes' }}</li>
            <li>{{ $counts['suggestions'] }} {{ $counts['suggestions'] === 1 ? 'suggestion' : 'suggestions' }}
                shown to you, with their reasons</li>
            <li>all {{ $counts['entries'] }} ledger {{ $counts['entries'] === 1 ? 'entry' : 'entries' }}</li>
            <li>any short-lived held context, purged and verified gone</li>
        </ul>

        <p class="f-body mt-16 max-w-[560px] opacity-80">
            One receipt stays in the ledger — that a deletion of this size
            happened, nothing about what it was. You can delete the receipt
            too. Your account and the memory agreement itself remain; if you
            would rather end the agreement as well,
            <a class="underline" href="{{ route('account.consent.revoke') }}">revoke it instead</a>.
        </p>

        <p class="f-body mt-16 max-w-[560px] opacity-80">
            If you want a copy first,
            <a class="underline" href="{{ route('account.export') }}">export your memory</a>
            before deleting.
        </p>

        <form class="mt-32" method="POST" action="{{ route('account.memory.destroy') }}">
            @csrf
            @method('DELETE')
            <button class="f-ui-01 border border-black px-24 py-10" type="submit">
                Delete everything
            </button>
        </form>

        <nav class="f-ui-01 border-dark-soft mt-32 border-t pt-24">
            <a class="underline" href="{{ route('account.memory') }}">Keep it — back to your memory</a>
        </nav>
    </div>
@endsection
