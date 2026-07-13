@props(['claim'])

{{-- One grounded claim: the statement verbatim, with the canonical fields
     behind it a quiet disclosure away. --}}
<div class="mb-16">
    <p class="f-body">{{ $claim->statement }}</p>
    <details class="f-ui-01 mt-4">
        <summary class="cursor-pointer underline opacity-60">Grounded in</summary>
        <ul class="mt-8 space-y-4 opacity-60">
            @foreach ($claim->citations as $citation)
                <li>
                    {{ ucfirst(str_replace('_', ' ', $citation->field)) }}:
                    “{{ \Illuminate\Support\Str::limit($citation->value, 80) }}”
                </li>
            @endforeach
        </ul>
    </details>
</div>
