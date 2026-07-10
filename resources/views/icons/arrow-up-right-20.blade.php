@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M14.5 5.5L5 15M14.5 5.5V12M14.5 5.5H8" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
