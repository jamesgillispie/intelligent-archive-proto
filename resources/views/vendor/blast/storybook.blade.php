<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <!-- Styles -->
    @vite('resources/frontend/styles/app.css')

    <!-- Scripts -->
    @vite('resources/frontend/scripts/app.js')

    @if (!empty($css))
        @foreach ($css as $key => $asset)
            <link rel="stylesheet" href="{{ $asset }}">
        @endforeach
    @endif

    @if ($canvasBgColor)
        <style>
            .sb-show-main {
                background-color: {{ $canvasBgColor }}
            }
        </style>
    @endif
</head>

<body>
@extends('vitrine-ui::components.icon.sprite')
@include('stories.'. $component)
@stack('modals')
@if (!empty($js))
    @foreach ($js as $key => $asset)
        <script src="{{ $asset }}"></script>
    @endforeach
@endif
</body>
</html>
