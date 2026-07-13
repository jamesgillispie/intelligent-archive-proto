@props(['artwork'])

<a class="group block" href="{{ route('archive.artworks.show', $artwork->slug) }}">
    <div class="aspect-[4/5] overflow-hidden bg-[rgba(0,0,0,0.04)]">
        <x-archive.artwork-image :artwork="$artwork" :widths="[440, 880]" sizes="(min-width: 900px) 25vw, 50vw"
            :decorative="true"
            img-class="h-full w-full object-contain transition-opacity duration-300 group-hover:opacity-90" />
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
