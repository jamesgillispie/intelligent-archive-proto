@storybook([
    'layout' => 'fullscreen',
    'name' => 'Footer',
    'preset' => 'navigation.footer',
])
<x-helpers.story-decorator :spacing="false" :container="false">
    <x-navigation.footer :nav="$nav ?? []" />
</x-helpers.story-decorator>
