@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <rect x="4" y="1" width="4" height="18" rx="1" fill="currentColor" />
        <rect x="12" y="1" width="4" height="18" rx="1" fill="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
