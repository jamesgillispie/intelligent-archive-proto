@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M1 5.61667L4.41177 9L11 2" stroke="currentColor" stroke-width="1.5" />
    </g>
@endPushOnce

<x-vui-icon-output :size="12" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
