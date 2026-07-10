@storybook([
    'layout' => 'fullscreen',
    'name' => 'Block Video',
    'argTypes' => [
        'variant' => [
            'control' => 'radio',
            'options' => ['default', 'large'],
        ],
    ],
    'args' => [
        'title' => 'Title that can over multiple lines',
        'description' => 'Description that can also be over multiple lines',
        'image' => [
            'src' => '/static/solution-1.jpg',
            'alt' => 'Solution 1',
        ],
        'video' => [
            'id' => 'Xy9kmhWkLZY',
            'type' => 'youtube',
            'autoplay' => true,
        ],
        'variant' => 'default',
    ],
])
<x-helpers.story-decorator>
    <x-blocks::video-block :title="$title" :description="$description" :video="$video" :image="$image" :variant="$variant" />
</x-helpers.story-decorator>
