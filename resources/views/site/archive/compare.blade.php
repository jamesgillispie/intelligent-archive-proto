@extends('layouts.main', ['page_id' => 'page-archive-compare'])

@section('content')
    <div class="mx-auto w-full max-w-[1100px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <nav class="f-ui-01 pb-24">
            @if ($trail)
                <a class="underline opacity-60" href="{{ route('account.trails.show', $trail->id) }}">←
                    “{{ $trail->title }}”</a>
            @else
                <a class="underline opacity-60" href="{{ route('account.trails') }}">← Your trails</a>
            @endif
        </nav>

        <x-memory.status />

        <header class="pb-32">
            <h1 class="f-heading-01">Side by side</h1>
            <p class="f-body mt-16 max-w-[640px] opacity-80">
                What the collection record actually says about these works —
                each claim grounded in the fields it came from, nothing
                invented to fill the space.
            </p>
        </header>

        <div class="grid grid-cols-2 gap-16 md:grid-cols-4">
            @foreach ($artworks as $artwork)
                <div>
                    <x-archive.artwork-card :artwork="$artwork" />
                    @if ($trail && !$trail->containsArtwork($artwork))
                        <form class="mt-8" method="POST" action="{{ route('account.trails.items.store') }}">
                            @csrf
                            <input name="trail_id" type="hidden" value="{{ $trail->id }}" />
                            <input name="artwork_id" type="hidden" value="{{ $artwork->id }}" />
                            <button class="f-ui-01 underline" type="submit">Save to “{{ $trail->title }}”</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <section class="border-dark-soft mt-32 border-t pt-24">
            <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Shared ground</h2>
            @forelse ($comparison->similarities as $claim)
                <x-archive.comparison-claim :claim="$claim" />
            @empty
                <p class="f-body opacity-60">The record holds no common ground it can vouch for.</p>
            @endforelse
        </section>

        <section class="border-dark-soft mt-32 border-t pt-24">
            <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Where they differ</h2>
            @forelse ($comparison->differences as $claim)
                <x-archive.comparison-claim :claim="$claim" />
            @empty
                <p class="f-body opacity-60">No differences the record can vouch for.</p>
            @endforelse
        </section>

        <p class="f-ui-01 mt-32 opacity-60">
            Drawn from the collection record — never from your activity.
            Asking for this comparison is itself noted in
            <a class="underline" href="{{ route('account.memory') }}">your memory</a>,
            because it was an explicit act.
        </p>
    </div>
@endsection
