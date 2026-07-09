@props(['artwork'])

<a class="group block" href="{{ route('archive.artworks.show', $artwork->slug) }}">
    <div class="aspect-[4/5] overflow-hidden bg-[rgba(0,0,0,0.04)]">
        @if ($artwork->primaryImage?->iiif_url)
            <img class="h-full w-full object-contain transition-opacity duration-300 group-hover:opacity-90"
                src="{{ $artwork->primaryImage->sizedUrl(440) }}"
                srcset="{{ $artwork->primaryImage->sizedUrl(440) }} 440w, {{ $artwork->primaryImage->sizedUrl(880) }} 880w"
                alt="{{ $artwork->primaryImage->altText($artwork->title) }}" sizes="(min-width: 900px) 25vw, 50vw"
                loading="lazy" />
        @endif
    </div>
    <div class="pt-8">
        <p class="f-ui-01">{{ $artwork->title }}</p>
        <p class="f-ui-01 opacity-60">
            {{ $artwork->attribution }}@if ($artwork->display_date)
                , {{ $artwork->display_date }}
            @endif
        </p>
    </div>
</a>
