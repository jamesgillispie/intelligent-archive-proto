@extends('layouts.main', ['page_id' => 'page-login'])

@section('content')
    <div class="mx-auto w-full max-w-[520px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Sign in</h1>
            <p class="f-body mt-16 opacity-80">
                Signing in turns your memory back on — your trails, saves and
                notes pick up where you left them.
            </p>
        </header>

        @if (session('status'))
            <p class="f-ui-01 border-dark-soft mb-24 border p-16" role="status">{{ session('status') }}</p>
        @endif

        <form class="flex flex-col gap-20" method="POST" action="{{ route('login') }}">
            @csrf

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Email</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="email" type="email"
                    value="{{ old('email') }}" autocomplete="email" required autofocus />
                @error('email')
                    <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Password</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="password" type="password"
                    autocomplete="current-password" required />
                @error('password')
                    <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label class="f-ui-01 flex items-center gap-8">
                <input name="remember" type="checkbox" value="1" />
                Stay signed in
            </label>

            <button class="f-ui-01 self-start border border-black px-24 py-10" type="submit">Sign in</button>
        </form>

        <nav class="f-ui-01 mt-32 flex flex-col gap-8">
            <a class="underline" href="{{ route('register') }}">New here? Create an account and choose memory</a>
            <a class="underline" href="{{ route('password.request') }}">Forgot your password?</a>
        </nav>
    </div>
@endsection
