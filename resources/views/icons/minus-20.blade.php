@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M17.5 10H2.5" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
