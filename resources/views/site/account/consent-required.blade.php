@extends('layouts.main', ['page_id' => 'page-consent-required'])

@section('content')
    <div class="mx-auto w-full max-w-[640px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">The memory agreement has changed</h1>
            <p class="f-body mt-16">
                The wording you originally agreed to has been updated since you
                last accepted it. Nothing of yours has been deleted — but memory
                writes stay off until you renew your agreement.
            </p>
            <p class="f-body mt-12 opacity-80">
                Renewing consent will be available here together with the memory
                dashboard. Until then you can keep browsing the archive as ever.
            </p>
        </header>

        <nav class="f-ui-01 flex gap-24">
            <a class="underline" href="{{ route('archive.index') }}">Browse the archive</a>
        </nav>
    </div>
@endsection
