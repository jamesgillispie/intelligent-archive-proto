@extends('layouts.main', ['page_id' => 'page-reset-password'])

@section('content')
    <div class="mx-auto w-full max-w-[520px] px-16 pb-64 pt-40 md:px-20 lg:pt-64">
        <header class="pb-24">
            <h1 class="f-heading-01">Choose a new password</h1>
        </header>

        <form class="flex flex-col gap-20" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input name="token" type="hidden" value="{{ $request->route('token') }}" />

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Email</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="email" type="email"
                    value="{{ old('email', $request->email) }}" autocomplete="email" required />
                @error('email')
                    <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">New password</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="password" type="password"
                    autocomplete="new-password" required autofocus />
                @error('password')
                    <span class="f-ui-01 text-[#9b3324]" role="alert">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex flex-col gap-4">
                <span class="f-ui-01 opacity-60">Confirm new password</span>
                <input class="f-body border-dark-soft border bg-transparent px-12 py-10" name="password_confirmation"
                    type="password" autocomplete="new-password" required />
            </label>

            <button class="f-ui-01 self-start border border-black px-24 py-10" type="submit">Reset password</button>
        </form>
    </div>
@endsection
