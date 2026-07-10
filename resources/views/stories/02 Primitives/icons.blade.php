@storybook([
    'layout' => 'fullscreen',
    'name' => 'Icons',
])
@php
    $icons = ['arrow-down-20', 'chevron-down-12', 'minus-20', 'arrow-left-20', 'chevron-down-20', 'pause-20', 'arrow-right-20', 'chevron-up-12', 'pause-large-20', 'arrow-up-20', 'chevron-up-20', 'play-20', 'arrow-up-left-20', 'close-12', 'play-large-20', 'arrow-up-right-20', 'close-20', 'plus-20', 'check-12', 'download-20', 'search-20', 'check-20', 'menu-20'];
@endphp
<x-docs-page title="Icons">
    <div class="grid-layout">
        @foreach ($icons as $icon)
            <div class="grid-col-span-3 lg:grid-col-span-2">
                <div class="aspect-1/1 flex items-center justify-center border p-4">
                    <x-vui-icon :name="$icon" />
                </div>
                <span class="f-ui mt-4 text-[10px]">{{ $icon }}</span>
            </div>
        @endforeach
    </div>
</x-docs-page>
