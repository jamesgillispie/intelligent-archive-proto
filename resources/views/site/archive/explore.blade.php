@extends('layouts.main', ['page_id' => 'page-archive-explore'])

@section('content')
    <div class="mx-auto w-full max-w-[1600px] px-16 md:px-20 lg:px-32 xl:px-40">
        <header class="pb-32 pt-40 lg:pt-64">
            <nav class="f-ui-01 pb-24">
                <a class="underline opacity-60" href="{{ route('archive.index') }}">← The Archive</a>
            </nav>
            <h1 class="f-heading-01 max-w-[900px]">Explore by idea</h1>
            <p class="f-body mt-20 max-w-[640px]">
                Follow a subject, a material, a name — “storms at sea”, “oil on panel”,
                “Thomas Cole”. Results are drawn from the collection record and are the
                same for every visitor; each one explains why it appeared. Nothing you
                type here is remembered beyond this session.
            </p>

            <form class="mt-24 flex max-w-[640px] gap-12" method="GET" action="{{ route('archive.explore') }}">
                <input class="f-body border-dark-soft w-full border-b bg-transparent pb-8 outline-none" name="q"
                    type="text" value="{{ $query }}" aria-label="Explore the collection" maxlength="200"
                    placeholder="What are you curious about?" />
                <button class="f-ui-01 underline" type="submit">Explore</button>
            </form>

            @if ($earlierQueries !== [])
                <p class="f-ui-01 mt-16 opacity-60">
                    Earlier this session:
                    @foreach ($earlierQueries as $earlier)
                        <a class="underline"
                            href="{{ route('archive.explore', ['q' => $earlier]) }}">{{ $earlier }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </p>
            @endif

            @if ($trailSeeds !== [])
                <p class="f-ui-01 mt-16 opacity-60">
                    From your trails:
                    @foreach ($trailSeeds as $seed)
                        <a class="underline"
                            href="{{ route('archive.explore', ['q' => $seed]) }}">{{ $seed }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                    — starting points drawn from works you saved; results stay the same for everyone.
                </p>
            @endif
        </header>

        @if ($result !== null)
            <section class="border-dark-soft border-t py-32">
                @if ($matches !== [])
                    <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">
                        {{ count($matches) }} {{ Str::plural('work', count($matches)) }} for “{{ $query }}”
                    </h2>
                    <div class="grid grid-cols-2 gap-x-16 gap-y-32 md:grid-cols-4">
                        @foreach ($matches as $matched)
                            <x-archive.cited-match :artwork="$matched['artwork']" :match="$matched['match']" />
                        @endforeach
                    </div>
                @else
                    <p class="f-body max-w-[640px]">
                        Nothing surfaced for “{{ $query }}”. Try a subject, a material,
                        a classification, or an artist’s name — the record is literal
                        about what it knows.
                    </p>
                    @if ($startingPoints !== [])
                        <p class="f-ui-01 mt-16 opacity-60">
                            Or start from the collection itself:
                            @foreach ($startingPoints as $point)
                                <a class="underline"
                                    href="{{ route('archive.explore', ['q' => $point]) }}">{{ $point }}</a>{{ $loop->last ? '' : ', ' }}
                            @endforeach
                        </p>
                    @endif
                @endif
            </section>
        @endif
    </div>
@endsection
