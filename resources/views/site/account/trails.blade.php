@extends('layouts.main', ['page_id' => 'page-account-trails'])

@section('content')
    <div class="mx-auto w-full max-w-[900px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Your trails</h1>
            <p class="f-body mt-16 max-w-[640px] opacity-80">
                Trails are slow, deliberate paths through the archive — a place
                to gather artworks and notes around a question you are holding.
            </p>
        </header>

        <x-memory.status />

        @if ($paused)
            <x-memory.paused-notice />
        @endif

        @if ($continueTrail)
            <section class="border-dark-soft mb-32 border p-20">
                <h2 class="f-ui-01 uppercase tracking-wide opacity-60">Continue where you left off</h2>
                <p class="f-body mt-8">
                    <a class="underline"
                        href="{{ route('account.trails.show', $continueTrail->id) }}">{{ $continueTrail->title }}</a>
                    <span class="opacity-60">
                        · {{ $continueTrail->items_count }}
                        {{ $continueTrail->items_count === 1 ? 'work' : 'works' }} · last touched
                        {{ $continueTrail->last_activity_at->diffForHumans() }}
                    </span>
                </p>
            </section>
        @endif

        @if ($activeTrails->isNotEmpty())
            <section class="pb-32">
                <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Active trails</h2>
                <ul>
                    @foreach ($activeTrails as $trail)
                        <li class="border-dark-soft flex items-baseline justify-between gap-16 border-t py-16">
                            <div>
                                <a class="f-body underline"
                                    href="{{ route('account.trails.show', $trail->id) }}">{{ $trail->title }}</a>
                                @if ($trail->intent_note)
                                    <p class="f-ui-01 mt-4 opacity-60">{{ $trail->intent_note }}</p>
                                @endif
                            </div>
                            <span class="f-ui-01 shrink-0 opacity-60">
                                {{ $trail->items_count }} {{ $trail->items_count === 1 ? 'work' : 'works' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @unless ($paused)
            <section class="border-dark-soft border-t pb-32 pt-24">
                <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Start a trail</h2>
                <form class="flex max-w-[480px] flex-col gap-16" method="POST" action="{{ route('account.trails.store') }}">
                    @csrf

                    <label class="flex flex-col gap-4">
                        <span class="f-ui-01 opacity-60">What is this trail about?</span>
                        <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="title" type="text"
                            value="{{ old('title') }}" required />
                        @error('title')
                            <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                        @enderror
                    </label>

                    <label class="flex flex-col gap-4">
                        <span class="f-ui-01 opacity-60">An intent note, if it helps (optional)</span>
                        <textarea class="f-body border-dark-soft border bg-transparent px-12 py-10" name="intent_note" rows="2">{{ old('intent_note') }}</textarea>
                        @error('intent_note')
                            <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                        @enderror
                    </label>

                    <button class="f-ui-01 self-start border border-black px-24 py-10" type="submit">
                        Start a trail
                    </button>
                </form>
            </section>
        @endunless

        @if ($archivedTrails->isNotEmpty())
            <section class="border-dark-soft border-t pb-32 pt-24">
                <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Archived</h2>
                <ul>
                    @foreach ($archivedTrails as $trail)
                        <li class="flex items-baseline justify-between gap-16 py-8">
                            <a class="f-body underline opacity-60"
                                href="{{ route('account.trails.show', $trail->id) }}">{{ $trail->title }}</a>
                            <span class="f-ui-01 shrink-0 opacity-60">
                                {{ $trail->items_count }} {{ $trail->items_count === 1 ? 'work' : 'works' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <nav class="f-ui-01 border-dark-soft flex items-center gap-24 border-t pt-24">
            <a class="underline" href="{{ route('archive.index') }}">Browse the archive</a>
            <a class="underline" href="{{ route('account.memory') }}">View memory</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="underline opacity-60" type="submit">Sign out</button>
            </form>
        </nav>
    </div>
@endsection
