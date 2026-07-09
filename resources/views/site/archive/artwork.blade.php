@extends('layouts.main', ['page_id' => 'page-archive-artwork'])

@section('content')
    <article class="mx-auto w-full max-w-[1600px] px-16 md:px-20 lg:px-32 xl:px-40">
        <nav class="f-ui-01 pt-24">
            <a class="underline opacity-60" href="{{ route('archive.artworks.index') }}">← All artworks</a>
        </nav>

        <div class="grid gap-32 py-32 lg:grid-cols-[3fr_2fr] lg:gap-48">
            <figure>
                @if ($artwork->primaryImage?->iiif_url)
                    <img class="h-auto w-full" src="{{ $artwork->primaryImage->sizedUrl(800) }}"
                        srcset="{{ $artwork->primaryImage->sizedUrl(800) }} 800w, {{ $artwork->primaryImage->sizedUrl(1400) }} 1400w"
                        alt="{{ $artwork->primaryImage->altText($artwork->title) }}" sizes="(min-width: 900px) 60vw, 100vw" />
                    @if ($artwork->primaryImage->assistive_text)
                        <figcaption class="f-ui-01 mt-8 opacity-60">
                            {{ $artwork->primaryImage->assistive_text }}
                        </figcaption>
                    @endif
                @else
                    <div class="f-ui-01 flex aspect-[4/5] items-center justify-center bg-[rgba(0,0,0,0.04)] opacity-60">
                        No open-access image is available for this work.
                    </div>
                @endif
            </figure>

            <div class="flex flex-col gap-24">
                <header>
                    <h1 class="f-heading-01">{{ $artwork->title }}</h1>
                    <p class="f-body mt-8">
                        @foreach ($artwork->artists as $artist)
                            <a class="underline"
                                href="{{ route('archive.artists.show', $artist->slug) }}">{{ $artist->name }}</a>
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                        @if ($artwork->artists->isEmpty())
                            {{ $artwork->attribution }}
                        @endif
                        @if ($artwork->display_date)
                            <span class="opacity-60">· {{ $artwork->display_date }}</span>
                        @endif
                    </p>
                </header>

                @if ($artwork->description)
                    <div class="f-body">{!! $artwork->description !!}</div>
                @endif

                <dl class="f-ui-01 border-dark-soft grid grid-cols-[140px_1fr] gap-y-8 border-t pt-20">
                    @if ($artwork->medium_text)
                        <dt class="opacity-60">Medium</dt>
                        <dd>{{ $artwork->medium_text }}</dd>
                    @endif

                    @if ($artwork->dimensions)
                        <dt class="opacity-60">Dimensions</dt>
                        <dd>{{ $artwork->dimensions }}</dd>
                    @endif

                    @if ($artwork->classification)
                        <dt class="opacity-60">Classification</dt>
                        <dd>{{ $artwork->classification->name }}</dd>
                    @endif

                    @if ($artwork->department)
                        <dt class="opacity-60">Department</dt>
                        <dd>{{ $artwork->department->name }}</dd>
                    @endif

                    @if ($artwork->credit_line)
                        <dt class="opacity-60">Credit</dt>
                        <dd>{{ $artwork->credit_line }}</dd>
                    @endif

                    @if ($artwork->source_inscription)
                        <dt class="opacity-60">Inscription</dt>
                        <dd class="whitespace-pre-line">{{ $artwork->source_inscription }}</dd>
                    @endif
                </dl>

                <x-archive.source-citation :artwork="$artwork" />
            </div>
        </div>

        @if ($moreByArtist->isNotEmpty())
            <section class="border-dark-soft border-t py-32">
                <h2 class="f-ui-01 mb-16 uppercase tracking-wide opacity-60">
                    More by {{ $artwork->artists->pluck('name')->join(', ') }}
                </h2>
                <div class="grid grid-cols-2 gap-x-16 gap-y-32 md:grid-cols-4">
                    @foreach ($moreByArtist as $related)
                        <x-archive.artwork-card :artwork="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </article>
@endsection
