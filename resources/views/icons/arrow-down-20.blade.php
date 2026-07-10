@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M10 16L10 3M5 11.5L10 16.4999L14.9999 11.5" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
