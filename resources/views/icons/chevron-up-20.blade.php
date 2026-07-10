@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M16 13L10 7L4 13" stroke="currentColor" stroke-linecap="square" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
