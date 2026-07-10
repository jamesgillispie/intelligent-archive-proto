@storybook([
    'layout' => 'fullscreen',
    'name' => 'Block Image',
    'args' => [
        'media' => [
            'src' => '/static/placeholder.jpg',
            'alt' => 'Solution 1',
        ],
        'caption' => 'This is optionnal caption text',
    ],
])
<x-helpers.story-decorator>
    <x-blocks::image-block :media="$media" :caption="$caption" />
</x-helpers.story-decorator>
