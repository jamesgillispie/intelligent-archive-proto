@extends('layouts.main', [
    'page_id' => 'page-homepage',
])

@section('content')
    <div class="mx-auto w-full max-w-[1600px] px-16 md:px-20 lg:px-32 xl:px-40">
        <header class="pb-40 pt-48 lg:pb-56 lg:pt-96">
            <h1 class="f-heading-01 max-w-[900px]">
                {{ $hero['tagline'] ?: 'Trails, not feeds.' }}
            </h1>
            <p class="f-body mt-20 max-w-[640px]">
                Follow a subject, a material, a name through
                {{ number_format($artworkCount) }} works from the National Gallery of
                Art’s open collection. Every result explains why it appeared, and the
                same question gets the same answer for every visitor.
            </p>

            <form class="mt-40 flex max-w-[900px] items-baseline gap-16" role="search" method="GET"
                action="{{ route('archive.explore') }}">
                <label class="sr-only" for="home-search">Search the archive</label>
                <input class="f-heading-03 border-dark-soft w-full border-b bg-transparent pb-12 outline-none"
                    id="home-search" name="q" type="search" maxlength="200"
                    placeholder="What are you curious about?" />
                <button class="f-ui-01 underline" type="submit">Explore</button>
            </form>

            @if ($trailSeeds !== [])
                <p class="f-ui-01 mt-20 opacity-60">
                    From your trails:
                    @foreach ($trailSeeds as $seed)
                        <a class="underline"
                            href="{{ route('archive.explore', ['q' => $seed]) }}">{{ $seed }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                    — starting points drawn from works you saved; results stay the same for everyone.
                </p>
            @endif

            @if ($earlierQueries !== [])
                <p class="f-ui-01 mt-20 opacity-60">
                    Earlier this session:
                    @foreach ($earlierQueries as $earlier)
                        <a class="underline"
                            href="{{ route('archive.explore', ['q' => $earlier]) }}">{{ $earlier }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </p>
            @endif

            @if ($startingPoints !== [])
                <p class="f-ui-01 mt-20 opacity-60">
                    Or start from the collection itself:
                    @foreach ($startingPoints as $point)
                        <a class="underline"
                            href="{{ route('archive.explore', ['q' => $point]) }}">{{ $point }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </p>
            @endif

            @guest
                <p class="f-ui-01 mt-24 opacity-60">
                    Nothing you type here is remembered beyond this session.
                    <a class="underline" href="{{ route('login') }}">Sign in</a> to build trails
                    and choose what the archive remembers.
                </p>
            @endguest
        </header>

        @if ($recentTrails->isNotEmpty())
            <section class="border-dark-soft border-t py-32">
                <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Pick up where you left off</h2>
                <ul class="f-body max-w-[640px]">
                    @foreach ($recentTrails as $trail)
                        <li class="{{ $loop->first ? '' : 'mt-12' }}">
                            <a class="underline" href="{{ route('account.trails.show', $trail) }}">
                                Continue “{{ $trail->title }}”
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <nav class="border-dark-soft f-ui-01 flex flex-wrap gap-24 border-t py-32 pb-48" aria-label="The archive">
            <a class="underline" href="{{ route('archive.index') }}">Browse the archive</a>
            <a class="underline" href="{{ route('archive.artists.index') }}">Artists</a>
            <a class="underline" href="{{ route('methodology') }}">How memory works</a>
        </nav>
    </div>
@endsection
