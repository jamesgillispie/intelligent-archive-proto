@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M9.5 2.5L2.5 9.5M9.5 9.5L2.5 2.5" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="12" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
