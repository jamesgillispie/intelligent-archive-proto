@pushOnce('icon_sprite')
    <g id="{{ $name }}">
        <path fill-rule="evenodd" clip-rule="evenodd"
            d="M11.1612 18.1204V10.0592H13.4574L13.7617 7.28133H11.1612L11.1651 5.89096C11.1651 5.16644 11.2361 4.77823 12.3099 4.77823H13.7454V2H11.4489C8.69039 2 7.71947 3.34759 7.71947 5.6138V7.28165H6V10.0596H7.71947V18.1204H11.1612Z"
            fill="currentColor" />
    </g>
@endPushOnce

<x-vui-icon-output :size="20" :name="$name" :aria-label="$ariaLabel ?? null" {{ $attributes }} />
