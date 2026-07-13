@extends('layouts.main', ['page_id' => 'page-account-trails'])

@section('content')
    <div class="mx-auto w-full max-w-[900px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-32">
            <h1 class="f-heading-01">Your trails</h1>
            <p class="f-body mt-16 max-w-[640px]">
                Trails are slow, deliberate paths through the archive — a place
                to gather artworks, notes and comparisons around a question you
                are holding. Building them arrives in the next phase; your
                memory is already on.
            </p>
        </header>

        <nav class="f-ui-01 flex items-center gap-24">
            <a class="underline" href="{{ route('archive.index') }}">Browse the archive</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="underline opacity-60" type="submit">Sign out</button>
            </form>
        </nav>
    </div>
@endsection
