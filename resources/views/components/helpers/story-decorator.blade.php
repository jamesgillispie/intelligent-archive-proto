@props(['container' => true, 'spacing' => true, 'componentWrapperId' => true])
<div class="{{ $spacing ? 'py-40' : '' }} w-full flex-col" {{ $attributes }}>
    <div @class(['container' => $container])>
        <div @if ($componentWrapperId) id="component-wrapper" @endif>
            {{ $slot }}
        </div>
    </div>
</div>
<x-helpers.grid />
