@extends('layouts.main', ['page_id' => 'page-account-save'])

@section('content')
    <div class="mx-auto w-full max-w-[640px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <nav class="f-ui-01 pb-24">
            <a class="underline opacity-60" href="{{ route('archive.artworks.show', $artwork->slug) }}">
                ← Back to the artwork
            </a>
        </nav>

        <x-memory.status />

        <header class="flex gap-20 pb-32">
            <div class="w-[96px] shrink-0 md:w-[120px]">
                <x-archive.artwork-image :artwork="$artwork" :widths="[240]" sizes="120px" img-class="h-auto w-full" />
            </div>
            <div>
                <p class="f-ui-01 uppercase tracking-wide opacity-60">Save</p>
                <h1 class="f-heading-01">{{ $artwork->title }}</h1>
                <p class="f-ui-01 mt-4 opacity-60">
                    {{ $artwork->attribution }}@if ($artwork->display_date)
                        , {{ $artwork->display_date }}
                    @endif
                </p>
            </div>
        </header>

        @if ($paused)
            <x-memory.paused-notice />
        @else
            <section class="border-dark-soft border-t pb-32 pt-24">
                <h2 class="f-ui-01 mb-8 uppercase tracking-wide opacity-60">Save for later</h2>
                @if ($existingSave)
                    <p class="f-body opacity-80">
                        Already saved for later, on {{ $existingSave->created_at->format('j F Y') }}.
                    </p>
                @else
                    <p class="f-body mb-16 opacity-80">Keep this work in your memory without a trail.</p>
                    <form method="POST" action="{{ route('account.saves.store') }}">
                        @csrf
                        <input name="artwork_id" type="hidden" value="{{ $artwork->id }}" />
                        <button class="f-ui-01 border border-black px-24 py-10" type="submit">Save for later</button>
                    </form>
                @endif
            </section>

            <section class="border-dark-soft border-t pb-32 pt-24">
                <h2 class="f-ui-01 mb-8 uppercase tracking-wide opacity-60">Add to a trail</h2>
                @if ($trails->isEmpty())
                    <p class="f-body opacity-80">You have no active trails yet — start one below.</p>
                @else
                    <form class="flex flex-col gap-16" method="POST" action="{{ route('account.trails.items.store') }}">
                        @csrf
                        <input name="artwork_id" type="hidden" value="{{ $artwork->id }}" />

                        <div class="flex flex-col gap-8">
                            @foreach ($trails as $trail)
                                <label class="f-body flex items-center gap-8">
                                    <input name="trail_id" type="radio" value="{{ $trail->id }}"
                                        @disabled(in_array($trail->id, $inTrailIds)) required />
                                    {{ $trail->title }}
                                    @if (in_array($trail->id, $inTrailIds))
                                        <span class="f-ui-01 opacity-60">already in this trail</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('trail_id')
                            <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                        @enderror

                        <label class="flex flex-col gap-4">
                            <span class="f-ui-01 opacity-60">A note to keep with it (optional)</span>
                            <textarea class="f-body border-dark-soft border bg-transparent px-12 py-10" name="note" rows="2">{{ old('note') }}</textarea>
                            @error('note')
                                <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                            @enderror
                        </label>

                        <button class="f-ui-01 self-start border border-black px-24 py-10" type="submit">
                            Add to trail
                        </button>
                    </form>
                @endif
            </section>

            <section class="border-dark-soft border-t pt-24">
                <h2 class="f-ui-01 mb-8 uppercase tracking-wide opacity-60">Start a new trail with this work</h2>
                <form class="flex flex-col gap-16" method="POST" action="{{ route('account.trails.store') }}">
                    @csrf
                    <input name="artwork_id" type="hidden" value="{{ $artwork->id }}" />

                    <label class="flex flex-col gap-4">
                        <span class="f-ui-01 opacity-60">What is this trail about?</span>
                        <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="title"
                            type="text" value="{{ old('title') }}" required />
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
        @endif
    </div>
@endsection
