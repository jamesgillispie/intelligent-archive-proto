@if ($embed ?? null)
    <x-blocks::block type="embed" tag="figure">
        <div>
            {!! $embed !!}
        </div>
        @if ($caption ?? null)
            <figcaption class="">{{ $caption }}</figcaption>
        @endif
    </x-blocks::block>
@endif
