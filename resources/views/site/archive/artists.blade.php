@extends('layouts.main', ['page_id' => 'page-archive-artists'])

@section('content')
    <div class="mx-auto w-full max-w-[1600px] px-16 md:px-20 lg:px-32 xl:px-40">
        <header class="pb-24 pt-40">
            <h1 class="f-heading-01">Artists</h1>
            <p class="f-ui-01 mt-12 opacity-60">{{ number_format($artists->total()) }} artists with works in the archive</p>
        </header>

        <form class="border-dark-soft border-b pb-20" method="GET" action="{{ route('archive.artists.index') }}">
            <label class="flex max-w-[400px] items-end gap-12">
                <input class="f-ui-01 border-dark-soft w-full border bg-transparent px-10 py-6" name="q" type="search"
                    value="{{ $filters['q'] ?? '' }}" placeholder="Search artists" />
                <button class="f-ui-01 border border-black px-16 py-6" type="submit">Search</button>
            </label>
        </form>

        <ul class="grid grid-cols-1 gap-x-24 gap-y-16 py-32 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($artists as $artist)
                <li class="border-dark-soft border-b pb-12">
                    <a class="f-body underline" href="{{ route('archive.artists.show', $artist->slug) }}">
                        {{ $artist->name }}
                    </a>
                    <p class="f-ui-01 opacity-60">
                        @if ($artist->display_date)
                            {{ $artist->display_date }} ·
                        @endif
                        @if ($artist->nationality)
                            {{ $artist->nationality }} ·
                        @endif
                        {{ $artist->artworks_count }} {{ Str::plural('work', $artist->artworks_count) }}
                    </p>
                </li>
            @endforeach
        </ul>

        <div class="f-ui-01 pb-48">
            {{ $artists->onEachSide(1)->links() }}
        </div>
    </div>
@endsection
