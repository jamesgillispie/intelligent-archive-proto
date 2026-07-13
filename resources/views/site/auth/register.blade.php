@extends('layouts.main', ['page_id' => 'page-register'])

@section('content')
    <div class="mx-auto w-full max-w-[640px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Create an account</h1>
            <p class="f-body mt-16 opacity-80">
                You can browse the whole archive without one. An account exists
                for a single reason: to remember, with your consent.
            </p>
        </header>

        <x-memory.consent-banner />

        <form class="mt-32 flex flex-col gap-20" method="POST" action="{{ route('register') }}">
            @csrf

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Name</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="name" type="text"
                    value="{{ old('name') }}" autocomplete="name" required autofocus />
                @error('name')
                    <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Email</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="email" type="email"
                    value="{{ old('email') }}" autocomplete="email" required />
                @error('email')
                    <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Password</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="password" type="password"
                    autocomplete="new-password" required />
                @error('password')
                    <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Confirm password</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="password_confirmation"
                    type="password" autocomplete="new-password" required />
            </label>

            <button class="f-ui-01 self-start border border-black px-24 py-10" type="submit">
                Create account and turn memory on
            </button>
        </form>

        <nav class="f-ui-01 mt-32">
            <a class="underline" href="{{ route('login') }}">Already have an account? Sign in</a>
        </nav>
    </div>
@endsection
