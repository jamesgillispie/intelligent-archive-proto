@props(['artwork', 'match'])

{{-- One Nexus result: the work, the reason it appeared (shown verbatim),
     and a quiet disclosure quoting the canonical fields that ground it. --}}
<div>
    <x-archive.artwork-card :artwork="$artwork" />
    <p class="f-ui-01 mt-8 opacity-60">{{ $match->reason }}</p>
    <details class="f-ui-01 mt-4">
        <summary class="cursor-pointer underline opacity-60">Why this?</summary>
        <ul class="mt-8 space-y-4 opacity-60">
            @foreach ($match->citations as $citation)
                <li>
                    {{ ucfirst(str_replace('_', ' ', $citation->field)) }}:
                    “{{ \Illuminate\Support\Str::limit($citation->value, 80) }}”
                </li>
            @endforeach
        </ul>
    </details>
</div>
