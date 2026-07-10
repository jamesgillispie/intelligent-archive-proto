@props([
    'tag' => 'div',
    'type' => null,
])

<{{ $tag }} {{ $attributes->class(['block-el', 'block-el-' . $type => isset($type)]) }}>
    {{ $slot }}
    </{{ $tag }}>
