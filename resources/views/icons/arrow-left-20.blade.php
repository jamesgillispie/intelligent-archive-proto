@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M4 10H17M8.5 5L3.50006 10L8.5 14.9999" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
