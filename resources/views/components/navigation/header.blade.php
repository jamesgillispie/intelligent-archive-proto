@props([
    'nav' => [],
])

<header class="border-dark-soft border-b">
    <div
        class="mx-auto flex w-full max-w-[1600px] flex-wrap items-baseline justify-between gap-x-24 gap-y-8 px-16 py-20 md:px-20 lg:px-32 xl:px-40">
        <p class="f-ui-01">
            <a href="{{ route('home') }}">{{ config('app.name') }}</a>
        </p>
        <nav class="f-ui-01 flex flex-wrap items-baseline gap-x-20 gap-y-8" aria-label="Site">
            <a class="underline" href="{{ route('archive.index') }}">The Archive</a>
            <a class="underline" href="{{ route('archive.explore') }}">Explore</a>
            @auth
                <a class="underline" href="{{ route('account.trails') }}">Your trails</a>
                <a class="underline" href="{{ route('account.memory') }}">Your memory</a>
                <form class="inline" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="underline opacity-60" type="submit">Sign out</button>
                </form>
            @else
                <a class="underline opacity-60" href="{{ route('login') }}">Sign in</a>
            @endauth
        </nav>
    </div>
</header>
