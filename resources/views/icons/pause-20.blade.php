@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <rect x="5" y="4" width="3" height="12" rx="1" fill="currentColor" />
        <rect x="12" y="4" width="3" height="12" rx="1" fill="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
