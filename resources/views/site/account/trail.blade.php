@extends('layouts.main', ['page_id' => 'page-account-trail'])

@section('content')
    <div class="mx-auto w-full max-w-[900px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <nav class="f-ui-01 pb-24">
            <a class="underline opacity-60" href="{{ route('account.trails') }}">← Your trails</a>
        </nav>

        <x-memory.status />

        @if ($paused)
            <x-memory.paused-notice />
        @endif

        <header class="pb-32">
            @if ($trail->isArchived())
                <p class="f-ui-01 mb-8 uppercase tracking-wide opacity-60">Archived</p>
            @endif
            <h1 class="f-heading-01">{{ $trail->title }}</h1>
            @if ($trail->intent_note)
                <p class="f-body mt-16 max-w-[640px] opacity-80">{{ $trail->intent_note }}</p>
            @endif
            <p class="f-ui-01 mt-8 opacity-60">
                Started {{ $trail->created_at->format('j F Y') }} · {{ $trail->items->count() }}
                {{ $trail->items->count() === 1 ? 'work' : 'works' }}
            </p>
        </header>

        @if ($trail->items->isEmpty())
            <p class="f-body border-dark-soft border-t pt-24 opacity-80">
                Nothing here yet. Find a work in the
                <a class="underline" href="{{ route('archive.index') }}">archive</a>
                and choose “Save to trail”.
            </p>
        @else
            <ol>
                @foreach ($trail->items as $item)
                    <li
                        class="border-dark-soft grid grid-cols-[32px_96px_1fr] gap-16 border-t py-20 md:grid-cols-[48px_120px_1fr]">
                        <span class="f-ui-01 pt-4 opacity-60">{{ $loop->iteration }}</span>

                        <a href="{{ route('archive.artworks.show', $item->artwork->slug) }}">
                            <x-archive.artwork-image :artwork="$item->artwork" :widths="[240]" sizes="120px"
                                img-class="h-auto w-full" />
                        </a>

                        <div>
                            <p class="f-body">
                                <a class="underline"
                                    href="{{ route('archive.artworks.show', $item->artwork->slug) }}">{{ $item->artwork->title }}</a>
                            </p>
                            <p class="f-ui-01 opacity-60">
                                {{ $item->artwork->attribution }}@if ($item->artwork->display_date)
                                    , {{ $item->artwork->display_date }}
                                @endif
                            </p>

                            @if ($item->note)
                                <p class="f-body mt-8 max-w-[560px] whitespace-pre-line">{{ $item->note }}</p>
                            @endif

                            <div class="f-ui-01 mt-8 flex items-center gap-16 opacity-60">
                                <span>Added {{ $item->created_at->format('j M Y') }}</span>
                                @if ($item->origin === \App\Models\Memory\TrailItem::ORIGIN_SUGGESTION_ACCEPTED)
                                    <span>From a suggestion you accepted</span>
                                @endif
                                <form method="POST"
                                    action="{{ route('account.trails.items.destroy', [$trail->id, $item->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="underline" type="submit">Remove</button>
                                </form>
                                @if ($trail->items->count() >= 2)
                                    <label class="flex cursor-pointer items-center gap-6">
                                        <input name="works[]" form="compare-form" type="checkbox"
                                            value="{{ $item->artwork->id }}" />
                                        Compare
                                    </label>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>

            @if ($trail->items->count() >= 2)
                <form class="f-ui-01 mt-16" id="compare-form" method="GET" action="{{ route('archive.compare') }}">
                    <input name="trail" type="hidden" value="{{ $trail->id }}" />
                    <button class="underline" type="submit">Compare selected works</button>
                    <span class="opacity-60">— tick two or more works above.</span>
                </form>
                @error('works')
                    <p class="f-ui-01 mt-8 text-[#9b3324]" role="alert">{{ $message }}</p>
                @enderror
            @endif
        @endif

        @if ($canExpand || $continuationSuggestions->isNotEmpty() || $counterpointSuggestions->isNotEmpty())
            <section class="border-dark-soft mt-32 border-t pt-24" id="suggestions">
                <h2 class="f-ui-01 mb-8 uppercase tracking-wide opacity-60">Suggestions</h2>
                <p class="f-body mb-16 max-w-[640px] opacity-80">
                    Grounded in the works you chose — continuations that deepen
                    this thread, and counterpoints that broaden it. Each one
                    explains itself; dismiss it and it won’t be suggested again.
                </p>

                @if ($continuationSuggestions->isNotEmpty())
                    <h3 class="f-ui-01 mb-12 opacity-60">Continuations</h3>
                    <div class="grid grid-cols-2 gap-x-16 gap-y-32 md:grid-cols-3">
                        @foreach ($continuationSuggestions as $suggestion)
                            <x-memory.suggestion-card :recommendation="$suggestion" />
                        @endforeach
                    </div>
                @endif

                @if ($counterpointSuggestions->isNotEmpty())
                    <h3 class="f-ui-01 mb-12 mt-24 opacity-60">Counterpoints</h3>
                    <div class="grid grid-cols-2 gap-x-16 gap-y-32 md:grid-cols-3">
                        @foreach ($counterpointSuggestions as $suggestion)
                            <x-memory.suggestion-card :recommendation="$suggestion" />
                        @endforeach
                    </div>
                @endif

                @error('suggestion')
                    <p class="f-ui-01 mt-16 text-[#9b3324]" role="alert">{{ $message }}</p>
                @enderror

                @if ($canExpand)
                    <form class="mt-16" method="POST" action="{{ route('account.trails.expand', $trail->id) }}">
                        @csrf
                        <button class="f-ui-01 border border-black px-24 py-10" type="submit">
                            Expand this trail
                        </button>
                    </form>
                    @error('trail')
                        <p class="f-ui-01 mt-8 text-[#9b3324]" role="alert">{{ $message }}</p>
                    @enderror
                @endif
            </section>
        @endif

        <section class="border-dark-soft mt-32 border-t pt-24">
            <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Notes</h2>

            @forelse ($trail->notes as $note)
                <div class="mb-16">
                    <p class="f-body max-w-[640px] whitespace-pre-line">{{ $note->body }}</p>
                    <div class="f-ui-01 mt-4 flex items-center gap-16 opacity-60">
                        <span>{{ $note->created_at->format('j M Y') }}</span>
                        <form method="POST" action="{{ route('account.notes.destroy', $note->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="underline" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="f-body mb-16 opacity-60">No notes yet.</p>
            @endforelse

            @unless ($paused || $trail->isArchived())
                <form class="flex max-w-[480px] flex-col gap-12" method="POST"
                    action="{{ route('account.trails.notes.store', $trail->id) }}">
                    @csrf

                    <label class="flex flex-col gap-4">
                        <span class="f-ui-01 opacity-60">Add note</span>
                        <textarea class="f-body border-dark-soft border bg-transparent px-12 py-10" name="body" rows="3" required></textarea>
                        @error('body')
                            <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                        @enderror
                    </label>

                    <button class="f-ui-01 self-start border border-black px-24 py-10" type="submit">Add note</button>
                </form>
            @endunless
        </section>

        <section class="border-dark-soft mt-32 flex items-center gap-24 border-t pt-24">
            @unless ($paused)
                @if ($trail->isArchived())
                    <form method="POST" action="{{ route('account.trails.reactivate', $trail->id) }}">
                        @csrf
                        <button class="f-ui-01 underline" type="submit">Reactivate this trail</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('account.trails.archive', $trail->id) }}">
                        @csrf
                        <button class="f-ui-01 underline opacity-60" type="submit">Archive this trail</button>
                    </form>
                @endif
            @endunless

            <form method="POST" action="{{ route('account.trails.destroy', $trail->id) }}">
                @csrf
                @method('DELETE')
                <button class="f-ui-01 text-[#9b3324] underline" type="submit">
                    Delete this trail, its items and its notes
                </button>
            </form>
        </section>
    </div>
@endsection
