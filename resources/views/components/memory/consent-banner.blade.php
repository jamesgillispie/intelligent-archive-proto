{{-- Registration is the consent grant: the memory contract in plain language. --}}
<aside class="border-dark-soft border p-20" aria-labelledby="memory-consent-heading">
    <h2 class="f-ui-01 uppercase tracking-wide opacity-60" id="memory-consent-heading">
        What an account means
    </h2>
    <p class="f-body mt-12">
        An account turns on <strong>memory</strong>: the archive keeps the trails
        you build, the artworks you save, and the notes you write. Nothing more.
    </p>
    <ul class="f-body mt-12 flex list-disc flex-col gap-6 pl-20">
        <li>Only explicit actions are remembered — saving, adding to a trail, writing a note, asking for a comparison.
        </li>
        <li>Browsing, searching and looking are never recorded. There is no background profiling.</li>
        <li>You can inspect, pause, export or delete everything the archive remembers, at any time.</li>
    </ul>
    <p class="f-ui-01 mt-16 opacity-60">
        Memory agreement {{ config('memory.consent_version') }} ·
        <a class="underline" href="{{ route('methodology') }}">How memory works</a>
    </p>
</aside>
