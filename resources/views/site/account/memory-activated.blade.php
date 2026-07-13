@extends('layouts.main', ['page_id' => 'page-memory-activated'])

@section('content')
    <div class="mx-auto w-full max-w-[640px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Memory is now on</h1>
            <p class="f-body mt-16">
                From here on, the archive keeps what you choose to keep: the
                trails you build, the artworks you save, the notes you write.
                Nothing else is recorded, and you can inspect, pause or delete
                all of it whenever you like.
            </p>
        </header>

        <nav class="f-ui-01 flex gap-24">
            <a class="underline" href="{{ route('archive.index') }}">Continue to the archive</a>
            <a class="underline opacity-60" href="{{ route('account.trails') }}">Your trails</a>
            <a class="underline opacity-60" href="{{ route('account.memory') }}">View memory</a>
        </nav>
    </div>
@endsection
