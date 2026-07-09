@extends('layouts.main', ['page_id' => 'page-archive-artworks'])

@section('content')
    <div class="mx-auto w-full max-w-[1600px] px-16 md:px-20 lg:px-32 xl:px-40">
        <header class="pb-24 pt-40">
            <h1 class="f-heading-01">Artworks</h1>
            <p class="f-ui-01 mt-12 opacity-60">
                {{ number_format($artworks->total()) }} works
            </p>
        </header>

        <x-archive.filters :filters="$filters" :filter-options="$filterOptions" />

        @if ($artworks->isEmpty())
            <p class="f-body py-48">No artworks match these filters.</p>
        @else
            <div class="grid grid-cols-2 gap-x-16 gap-y-32 py-32 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($artworks as $artwork)
                    <x-archive.artwork-card :artwork="$artwork" />
                @endforeach
            </div>

            <div class="f-ui-01 pb-48">
                {{ $artworks->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endsection
