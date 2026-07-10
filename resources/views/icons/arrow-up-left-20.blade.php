@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M5.5 5.5L15 15M5.5 5.5V12M5.5 5.5H12" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
