@props(['artwork'])

{{-- Source citation: where this record came from, field by field.
     Citations over vibes (CLAUDE.md principle 5). --}}
<section class="border-dark-soft border-t pt-20">
    <h2 class="f-ui-01 mb-12 uppercase tracking-wide opacity-60">Source</h2>

    <dl class="f-ui-01 grid gap-y-6 md:grid-cols-[200px_1fr] md:gap-x-20">
        <dt class="opacity-60">Dataset</dt>
        <dd>
            National Gallery of Art Open Data
            (<a class="underline" href="https://github.com/NationalGalleryOfArt/opendata" rel="noopener">CC0</a>)
        </dd>

        <dt class="opacity-60">NGA object ID</dt>
        <dd>{{ $artwork->nga_object_id }}</dd>

        @if ($artwork->accession_number)
            <dt class="opacity-60">Accession number</dt>
            <dd>{{ $artwork->accession_number }}</dd>
        @endif

        @if ($artwork->sourceRecord)
            <dt class="opacity-60">Imported</dt>
            <dd>
                {{ $artwork->sourceRecord->first_seen_at?->format('j F Y') }}
                @if ($artwork->sourceRecord->version > 1)
                    (record version {{ $artwork->sourceRecord->version }})
                @endif
            </dd>
        @endif

        @if ($artwork->primaryImage)
            <dt class="opacity-60">Image rights</dt>
            <dd>Open access image, courtesy National Gallery of Art (CC0)</dd>
        @endif
    </dl>

    @php
        $overridden = collect($artwork->fieldProvenance())->filter(fn($p) => $p['overridden']);
    @endphp

    @if ($overridden->isNotEmpty())
        <div class="mt-16">
            <h3 class="f-ui-01 mb-8 opacity-60">Editorial notes</h3>
            <ul class="f-ui-01 flex flex-col gap-4">
                @foreach ($overridden as $field => $provenance)
                    <li>
                        The {{ str_replace('_', ' ', $field) }} shown has been refined editorially;
                        the dataset records it as
                        <span class="italic">“{{ $provenance['source_value'] }}”</span>.
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</section>
