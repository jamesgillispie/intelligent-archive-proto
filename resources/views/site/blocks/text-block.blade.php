<x-blocks::block type="text">
    <x-vui-wysiwyg>
        {!! $text ?? $slot  ?? '' !!}
    </x-vui-wysiwyg>
</x-blocks::block>
