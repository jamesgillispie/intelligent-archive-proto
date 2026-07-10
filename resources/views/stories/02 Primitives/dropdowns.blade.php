@storybook([
    'layout' => 'fullscreen',
    'name' => 'Dropdown',
    'args' => [
        'label' => 'Filter by subjects',
        'items' => [
            [
                'label' => 'History',
                'url' => '#',
            ],
            [
                'label' => 'Wars & Conflicts',
                'url' => '#',
            ],
            [
                'label' => 'World',
                'url' => '#',
            ],
        ],
    ],
])
<x-docs-page title="Dropdown">
    <div class="container">
        <x-vui-dropdown :label="$label ?? ''">
            @foreach ($items ?? [] as $item)
                <li>
                    <x-vui-link variant="on-listing" :href="$item['url'] ?? ''">
                        {{ $item['label'] ?? '' }}
                    </x-vui-link>
                </li>
            @endforeach
        </x-vui-dropdown>
    </div>
</x-docs-page>
