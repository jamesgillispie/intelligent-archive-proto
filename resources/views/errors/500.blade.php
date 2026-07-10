@extends('layouts.main', ['page_id' => 'page-404', 'page_background' => 'light'])

@section('content')
    @php
        $title = '500';
        $subtitle = 'Server Error';
        $button = [
            'label' => 'Go home',
            'url' => '/',
        ];
    @endphp

    <x-sections.error :title="$title" :subtitle="$subtitle" :button="$button" />
@endsection
