@props([
    'title' => null,
    'subtitle' => null,
    'button' => null,
])

<div class="md:grid-layout container">
    <div class="md:grid-col-span-4 md:grid-col-start-5 text-center">
        @if ($title ?? null)
            <div class="f-display-01 mt-120 md:mt-180">
                {!! $title !!}
            </div>
        @endif
        @if ($subtitle ?? null)
            <div class="f-subhead-01 mt-12">
                {!! $subtitle !!}
            </div>
        @endif
        @if ($button ?? null)
            <x-vui-button class="mb-100 md:mb-140 mx-auto mt-40 md:mt-60" :href="$button['url']" variant="primary"
                size="md">
                {!! $button['label'] !!}
            </x-vui-button>
        @endif
    </div>
</div>
