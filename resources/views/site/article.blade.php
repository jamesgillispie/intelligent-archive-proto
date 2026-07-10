@extends('layouts.main', ['page_id' => 'page-news-detail'])

@section('content')
    {{ $title }}

    {!! $page->renderBlocks() !!}
@endsection
