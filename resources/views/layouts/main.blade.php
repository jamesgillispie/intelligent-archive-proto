<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        // Fix for a possible bug on the meta package
        try {
            $meta = SEO::generate();
        } catch (\Throwable $_) {
            $meta = '';
        }
    @endphp

    {!! $meta !!}
    <x-partials::head-favicon />
    <x-partials::head-metas />
    <x-partials::head-assets />
</head>

<body class="{{ $page_id ?? null }} {{ $body_classes ?? 'flex flex-col min-h-screen' }}">
    <x-vui-button class="z-99999 absolute left-[-99999px] top-20 focus:left-20" href="#main" variant="secondary">
        {{ __('Skip to main content') }}
    </x-vui-button>

    <div class="flex flex-grow flex-col" id="pjax-wrapper">
        <x-navigation.header :nav="[]" />
        <div class="{{ $transitionPage ?? 'transition-page' }} flex h-full flex-grow flex-col">
            <main class="{{ $main_classes ?? 'flex flex-col flex-grow overflow-hidden' }}" id="main">
                @yield('content')

                @section('footer')
                    <x-navigation.footer />
                @show

                <div data-modal-wrapper>
                    @stack('modals')
                </div>
            </main>
        </div>
        <div data-global-modal-wrapper>
            @stack('global_modals')
        </div>

        <div id="icon-sprite">
            <svg xmlns="http://www.w3.org/2000/svg" hidden>
                <defs>
                    @stack('icon_sprite')
                </defs>
            </svg>
        </div>
    </div>
    <x-partials::cookie />
    <x-partials::google-analytics />
    <x-partials::clarity />

    <x-partials::footer-assets />

    @env(['local', 'dev', 'internal'])
    <x-helpers.grid />
    @endenv
</body>

</html>
