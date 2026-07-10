@storybook([
    'layout' => 'fullscreen',
    'name' => 'Pagination',
    'args' => [
        'pages' => [
            [
                'url' => '?page=1',
            ],
            [
                'url' => '?page=2',
            ],
            [
                'url' => '?page=3',
            ],
        ],
        'current_page' => 1,
        'last_page' => 3,
        'current_page_count' => 10,
        'total' => 25,
    ],
])
<x-helpers.story-decorator>
    <x-vui-pagination :pages="$pages ?? []" :currentPage="$current_page" :currentPageCount="$current_page_count" :lastPage="$last_page" :total="$total"
        btnVariant="secondary-icon" />
</x-helpers.story-decorator>
