@storybook([
    'layout' => 'fullscreen',
    'name' => 'Links',
])
<x-docs-page title="Links">
    <div class="block space-y-10">
        <div class="flex gap-x-10">
            <div class="p-4">
                <x-vui-link href="#" variant="standard">Link Standard</x-vui-link>
            </div>
            <div class="p-4">
                <x-vui-link href="#" variant="on-listing">Link on listing</x-vui-link>
            </div>
            <div class="p-4">
                <x-vui-link href="#" icon-position="after" icon="empty-20" variant="with-icon">Link with
                    icon</x-vui-link>
            </div>
        </div>
    </div>
</x-docs-page>
