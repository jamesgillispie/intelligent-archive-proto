@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path
            d="M18.5 9.13398C19.1667 9.51888 19.1667 10.4811 18.5 10.866L5 18.6603C4.33334 19.0452 3.5 18.564 3.5 17.7942L3.5 2.20577C3.5 1.43597 4.33333 0.954845 5 1.33975L18.5 9.13398Z"
            fill="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
