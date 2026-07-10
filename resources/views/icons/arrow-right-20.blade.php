@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M16 10H3M11.5 5L16.4999 10L11.5 14.9999" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
