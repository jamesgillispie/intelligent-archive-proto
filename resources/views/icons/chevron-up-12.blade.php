@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M10 8L6 4L2 8" stroke="currentColor" stroke-linecap="square" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
