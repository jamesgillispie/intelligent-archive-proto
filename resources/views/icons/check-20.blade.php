@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M4 10L8 14L16 6" stroke="currentColor" stroke-width="2" stroke-linecap="square" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
