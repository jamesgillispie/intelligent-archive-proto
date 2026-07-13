@extends('layouts.main', ['page_id' => 'page-archive'])

@section('content')
    <div class="mx-auto w-full max-w-[1600px] px-16 md:px-20 lg:px-32 xl:px-40">
        <header class="pb-32 pt-40 lg:pb-48 lg:pt-64">
            <h1 class="f-heading-01 max-w-[900px]">The Archive</h1>
            <p class="f-body mt-20 max-w-[640px]">
                {{ number_format($artworkCount) }} paintings from the National Gallery of Art’s
                open collection, presented for slow looking and unhurried research.
                Browse freely — nothing here is tracked or remembered without your consent.
            </p>
            <nav class="f-ui-01 mt-24 flex gap-24">
                <a class="underline" href="{{ route('archive.artworks.index') }}">
                    Browse artworks ({{ number_format($artworkCount) }})
                </a>
                <a class="underline" href="{{ route('archive.artists.index') }}">
                    Artists ({{ number_format($artistCount) }})
                </a>
                <a class="underline" href="{{ route('archive.explore') }}">
                    Explore by idea
                </a>
            </nav>
        </header>

        @if ($recent->isNotEmpty())
            <section class="pb-48">
                <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">Recently added</h2>
                <div class="grid grid-cols-2 gap-x-16 gap-y-32 md:grid-cols-4">
                    @foreach ($recent as $artwork)
                        <x-archive.artwork-card :artwork="$artwork" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
