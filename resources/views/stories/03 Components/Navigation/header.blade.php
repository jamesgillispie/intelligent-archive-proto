@storybook([
    'layout' => 'fullscreen',
    'name' => 'Header',
    'preset' => 'navigation.base',
])
<x-helpers.story-decorator :spacing="false" :container="false">
    <x-navigation.header :nav="$nav ?? []" :background="$background ?? 'light'" />
</x-helpers.story-decorator>
