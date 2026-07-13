@props([
    'nav' => [],
])
<footer class="border-dark-soft mt-auto border-t">
    <div
        class="f-ui-01 mx-auto flex w-full max-w-[1600px] flex-wrap items-baseline gap-x-24 gap-y-8 px-16 py-24 opacity-60 md:px-20 lg:px-32 xl:px-40">
        <a class="underline" href="{{ route('methodology') }}">How memory works</a>
        <a class="underline" href="{{ route('about.index') }}">About</a>
        <span>
            Collection data and images courtesy of the National Gallery of Art open-data program.
        </span>
    </div>
</footer>
