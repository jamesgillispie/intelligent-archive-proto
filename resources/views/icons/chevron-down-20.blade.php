@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M16 7L10 13L4 7" stroke="currentColor" stroke-linecap="square" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
