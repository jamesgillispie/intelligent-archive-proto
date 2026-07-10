@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M8.5 10.5 3.5 6 8.5 1.5" fill="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="12" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
