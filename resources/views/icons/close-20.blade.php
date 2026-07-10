@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M17 3L3 17M17 17L3 3" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
