<x-blocks::block type="video">
    @if (($title ?? null) || ($description ?? null))
        <div>
            @if ($title ?? null)
                <x-vui-heading class="" level="3">{!! $title !!}</x-vui-heading>
            @endif
            @if ($description ?? null)
                <x-vui-wysiwyg>
                    {!! contentToParagraph($description) !!}
                </x-vui-wysiwyg>
            @endif
        </div>
    @endif

    @if ($video ?? null)
        <x-vui-media class="aspect-16/9" :video="$video" image-preset="block-video" :image="$media ?? null" videoPlayIcon="play-large-20" />
    @endif
</x-blocks::block>
