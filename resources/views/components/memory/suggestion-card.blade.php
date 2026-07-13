@props(['recommendation'])

{{-- One stored suggestion: the work, the reason it was suggested (shown
     verbatim from the Recommendation row), a full account of why — the
     evidence and the context the query ran from — and the two answers.
     Nothing here is recomputed; this is what the system actually did. --}}
<div>
    <x-archive.artwork-card :artwork="$recommendation->artwork" />
    <p class="f-ui-01 mt-8 opacity-60">{{ $recommendation->reason }}</p>

    <details class="f-ui-01 mt-4">
        <summary class="cursor-pointer underline opacity-60">Why this suggestion?</summary>
        <div class="mt-8 space-y-8 opacity-60">
            @php
                $context = $recommendation->context ?? [];
                $anchors = array_slice((array) ($context['anchors']['artists'] ?? []), 0, 4);
                $respected = (int) ($context['dismissals_respected'] ?? 0);
            @endphp
            <p>
                Drawn from “{{ $context['trail_title'] ?? '' }}” as it stood —
                {{ $context['item_count'] ?? '?' }} works you
                chose{{ $anchors !== [] ? ', anchored on ' . implode(', ', $anchors) : '' }}.
                @if ($respected > 0)
                    {{ $respected }} {{ $respected === 1 ? 'work you dismissed was' : 'works you dismissed were' }}
                    left out.
                @endif
            </p>
            <ul class="space-y-4">
                @foreach ((array) $recommendation->citations as $citation)
                    <li>
                        {{ ucfirst(str_replace('_', ' ', $citation['field'] ?? '')) }}:
                        “{{ \Illuminate\Support\Str::limit($citation['value'] ?? '', 80) }}”
                    </li>
                @endforeach
            </ul>
        </div>
    </details>

    <div class="f-ui-01 mt-8 flex items-center gap-16">
        <form method="POST" action="{{ route('account.suggestions.accept', $recommendation->id) }}">
            @csrf
            <button class="underline" type="submit"
                aria-label="Save “{{ $recommendation->artwork->title }}” to this trail">
                Save to this trail
            </button>
        </form>
        <form method="POST" action="{{ route('account.suggestions.dismiss', $recommendation->id) }}">
            @csrf
            <button class="underline opacity-60" type="submit"
                aria-label="Dismiss the suggestion of “{{ $recommendation->artwork->title }}”">
                Dismiss
            </button>
        </form>
    </div>
</div>
