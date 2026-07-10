@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M10 4L10 17M15 8.5L10 3.50006L5.00006 8.5" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
