@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M1 5H19M1 10H19M1 15H19" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
