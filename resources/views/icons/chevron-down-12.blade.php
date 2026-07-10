@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M10 4L6 8L2 4" stroke="currentColor" stroke-linecap="square" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
