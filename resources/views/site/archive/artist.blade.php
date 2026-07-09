@extends('layouts.main', ['page_id' => 'page-archive-artist'])

@section('content')
    <div class="mx-auto w-full max-w-[1600px] px-16 md:px-20 lg:px-32 xl:px-40">
        <nav class="f-ui-01 pt-24">
            <a class="underline opacity-60" href="{{ route('archive.artists.index') }}">← All artists</a>
        </nav>

        <header class="pb-24 pt-24">
            <h1 class="f-heading-01">{{ $artist->name }}</h1>
            <p class="f-ui-01 mt-12 opacity-60">
                @if ($artist->display_date)
                    {{ $artist->display_date }}
                @endif
                @if ($artist->nationality)
                    · {{ $artist->nationality }}
                @endif
            </p>
            @if ($artist->description)
                <div class="f-body mt-20 max-w-[640px]">{!! $artist->description !!}</div>
            @endif
        </header>

        <section class="border-dark-soft border-t py-32">
            <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">
                {{ number_format($artworks->total()) }} {{ Str::plural('work', $artworks->total()) }} in the archive
            </h2>
            <div class="grid grid-cols-2 gap-x-16 gap-y-32 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($artworks as $artwork)
                    <x-archive.artwork-card :artwork="$artwork" />
                @endforeach
            </div>

            <div class="f-ui-01 pt-32">
                {{ $artworks->onEachSide(1)->links() }}
            </div>
        </section>
    </div>
@endsection
