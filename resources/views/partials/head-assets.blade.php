@if (config('app.env') !== 'local')
    {{--    Here you should preload some of your assets like fonts, images, etc. --}}

    {{--    <link type="font/woff2" --}}
    {{--          href="{{ Vite::asset('resources/frontend/fonts/Lateral-Standard/Lateral-StandardRegular.woff2') }}" --}}
    {{--          rel="preload" --}}
    {{--          as="font" --}}
    {{--          crossorigin=""> --}}
@endif

<!-- Styles -->
@stack('styles')
@vite('resources/frontend/styles/app.css')
