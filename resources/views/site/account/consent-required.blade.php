@extends('layouts.main', ['page_id' => 'page-consent-required'])

@section('content')
    <div class="mx-auto w-full max-w-[640px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">The memory agreement has changed</h1>
            <p class="f-body mt-16">
                The wording you originally agreed to has been updated since you
                last accepted it. Nothing of yours has been deleted — but memory
                writes stay off until you renew your agreement.
            </p>
        </header>

        <x-memory.consent-banner />

        <form class="mt-24" method="POST" action="{{ route('account.consent.renew') }}">
            @csrf
            <button class="f-ui-01 border border-black px-24 py-10" type="submit">
                Renew the agreement and turn memory back on
            </button>
        </form>

        <nav class="f-ui-01 mt-32 flex gap-24">
            <a class="underline" href="{{ route('account.memory') }}">View your memory first</a>
            <a class="underline opacity-60" href="{{ route('archive.index') }}">Browse the archive</a>
        </nav>
    </div>
@endsection
