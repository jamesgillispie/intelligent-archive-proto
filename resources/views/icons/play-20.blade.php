@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path
            d="M14.5 9.13397C15.1667 9.51888 15.1667 10.4811 14.5 10.866L7 15.1962C6.33333 15.5811 5.5 15.0999 5.5 14.3301L5.5 5.66987C5.5 4.90007 6.33333 4.41895 7 4.80385L14.5 9.13397Z"
            fill="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
