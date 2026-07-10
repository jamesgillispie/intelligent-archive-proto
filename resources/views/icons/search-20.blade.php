@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <circle cx="8.5" cy="8.5" r="7" stroke="currentColor" />
        <path d="M18.5 18.5L13.5 13.5" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
