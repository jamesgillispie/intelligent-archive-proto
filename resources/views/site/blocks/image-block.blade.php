<x-blocks::block type="image" tag="figure">
    @if ($title ?? null)
        <x-vui-heading level="3">{{ $title }}</x-vui-heading>
    @endif
    @if ($description ?? null)
        <x-vui-wysiwyg>
            {{ contentToParagraph($description) }}
        </x-vui-wysiwyg>
    @endif

    <x-vui-media :image="$media" image-preset="block-image" :cover="true" :image-options="[
        'class' => 'object-cover rounded-md',
        'loading' => 'lazy',
    ]" />

    @if ($caption ?? null)
        <div class="">
            {!! $caption !!}
        </div>
    @endif
</x-blocks::block>
