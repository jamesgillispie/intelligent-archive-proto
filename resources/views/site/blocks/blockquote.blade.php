<x-blocks::block type="blockquote">
    <blockquote>
        <p>{{ $quote }}</p>
    </blockquote>
    @if ($caption ?? null)
        <p>{{ $caption }}</p>
    @endif
</x-blocks::block>
