@extends('layouts.main', ['page_id' => 'page-account-memory'])

@section('content')
    <div class="mx-auto w-full max-w-[900px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Your memory</h1>
            <p class="f-body mt-16 max-w-[640px] opacity-80">
                Everything the archive holds about you is on this page. Each
                entry exists because of something you explicitly did, and each
                can be deleted. There is no background profile to show, because
                there is none.
            </p>
        </header>

        <x-memory.status />

        <section class="border-dark-soft mb-24 border p-20">
            <h2 class="f-ui-01 uppercase tracking-wide opacity-60">Agreement</h2>
            @if ($consent)
                <p class="f-body mt-8">
                    You accepted memory agreement {{ $consent->consent_version }} on
                    {{ $consent->granted_at->format('j F Y') }}.
                    @unless ($consentCurrent)
                        The wording has since changed —
                        <a class="underline" href="{{ route('account.consent') }}">review and renew it</a>.
                        New memory stays off until you do; everything below is untouched.
                    @endunless
                </p>
            @endif
            <p class="f-ui-01 mt-8">
                <a class="underline opacity-60" href="/methodology">How memory works</a>
            </p>
        </section>

        <section class="border-dark-soft mb-32 border p-20">
            <h2 class="f-ui-01 uppercase tracking-wide opacity-60">Memory switch</h2>
            @if ($paused)
                <p class="f-body mt-8">
                    Memory is paused. Nothing new is being kept; everything below stays yours to keep or delete.
                </p>
                <form class="mt-16" method="POST" action="{{ route('account.memory.resume') }}">
                    @csrf
                    <button class="f-ui-01 border border-black px-24 py-10" type="submit">Resume memory</button>
                </form>
            @else
                <p class="f-body mt-8">
                    Memory is on: the saves, trails and notes you make are kept — and nothing else.
                </p>
                <form class="mt-16" method="POST" action="{{ route('account.memory.pause') }}">
                    @csrf
                    <button class="f-ui-01 border border-black px-24 py-10" type="submit">Pause memory</button>
                </form>
            @endif
        </section>

        <section class="mb-32">
            <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Saved for later</h2>
            @forelse ($saves as $save)
                <div class="border-dark-soft flex items-baseline justify-between gap-16 border-t py-12">
                    <a class="f-body underline"
                        href="{{ route('archive.artworks.show', $save->artwork->slug) }}">{{ $save->artwork->title }}</a>
                    <span class="f-ui-01 flex shrink-0 items-center gap-16 opacity-60">
                        {{ $save->created_at->format('j M Y') }}
                        <form method="POST" action="{{ route('account.saves.destroy', $save->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="underline" type="submit">Remove</button>
                        </form>
                    </span>
                </div>
            @empty
                <p class="f-body opacity-60">No saves outside trails.</p>
            @endforelse
        </section>

        <section class="mb-32">
            <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Trails and notes</h2>
            <p class="f-body">
                {{ $trailCount }} {{ $trailCount === 1 ? 'trail' : 'trails' }} —
                <a class="underline" href="{{ route('account.trails') }}">view and manage them</a>.
                {{ $noteCount }} {{ $noteCount === 1 ? 'note lives' : 'notes live' }} inside them.
            </p>
        </section>

        <section>
            <h2 class="f-ui-01 mb-8 uppercase tracking-wide opacity-60">The ledger</h2>
            <p class="f-body mb-16 max-w-[640px] opacity-80">
                A plain record of every explicit action — the only inputs any
                future suggestion will ever draw on. Export and whole-memory
                deletion arrive with the next phase.
            </p>

            @if ($events->isEmpty())
                <p class="f-body opacity-60">Nothing recorded yet.</p>
            @else
                <ul>
                    @foreach ($events as $event)
                        <li class="border-dark-soft flex items-baseline justify-between gap-16 border-t py-12">
                            <span class="f-body">{{ $event->description() }}</span>
                            <span class="f-ui-01 flex shrink-0 items-center gap-16 opacity-60">
                                {{ $event->occurred_at->format('j M Y, H:i') }}
                                <form method="POST" action="{{ route('account.memory.events.destroy', $event->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="underline" type="submit">Delete</button>
                                </form>
                            </span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-24">
                    {{ $events->onEachSide(1)->links() }}
                </div>
            @endif
        </section>

        <nav class="f-ui-01 border-dark-soft mt-32 flex items-center gap-24 border-t pt-24">
            <a class="underline" href="{{ route('account.trails') }}">Your trails</a>
            <a class="underline" href="{{ route('archive.index') }}">Browse the archive</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="underline opacity-60" type="submit">Sign out</button>
            </form>
        </nav>
    </div>
@endsection
