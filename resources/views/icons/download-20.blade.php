@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path d="M10.5 14V2M6.00007 10L10.5 14.4999L15 10M4 17.5H17" stroke="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
