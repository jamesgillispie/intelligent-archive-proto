<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-partials::head-assets />
</head>

<body class="'flex min-h-screen' flex-col">
    <div class="flex flex-grow flex-col">
        <div class="flex h-full flex-grow flex-col">
            <main class="'flex flex-grow' flex-col" id="main">
                <svg xmlns="http://www.w3.org/2000/svg" hidden>
                    <defs>
                        @stack('icon_sprite')
                    </defs>
                </svg>

                @yield('content')
            </main>
        </div>
    </div>
    <x-partials::footer-assets />
</body>

</html>
