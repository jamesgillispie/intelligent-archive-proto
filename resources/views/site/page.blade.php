@extends('layouts.main', [
    'page_id' => 'page-generic',
])

@section('content')
    {{ $title }}

    {!! $page->renderBlocks() !!}
@endsection
