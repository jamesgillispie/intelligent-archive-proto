@extends('layouts.main', ['page_id' => 'page-404', 'page_background' => 'light'])

@section('content')
    @php
        $title = '404';
        $subtitle = 'Sorry, it seems like the page you’re looking for isn’t here.';
        $button = [
            'label' => 'Go home',
            'url' => '/',
        ];
    @endphp

    <x-sections.error :title="$title" :subtitle="$subtitle" :button="$button" />
@endsection
