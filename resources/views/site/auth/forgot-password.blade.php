@extends('layouts.main', ['page_id' => 'page-forgot-password'])

@section('content')
    <div class="mx-auto w-full max-w-[520px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Reset your password</h1>
            <p class="f-body mt-16 opacity-80">
                Tell us the email on your account and we will send a link to
                choose a new password.
            </p>
        </header>

        @if (session('status'))
            <p class="f-ui-01 border-dark-soft mb-24 border p-16" role="status">{{ session('status') }}</p>
        @endif

        <form class="flex flex-col gap-20" method="POST" action="{{ route('password.email') }}">
            @csrf

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Email</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="email" type="email"
                    value="{{ old('email') }}" autocomplete="email" required autofocus />
                @error('email')
                    <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <button class="f-ui-01 self-start border border-black px-24 py-10" type="submit">Send reset link</button>
        </form>

        <nav class="f-ui-01 mt-32">
            <a class="underline" href="{{ route('login') }}">Back to sign in</a>
        </nav>
    </div>
@endsection
