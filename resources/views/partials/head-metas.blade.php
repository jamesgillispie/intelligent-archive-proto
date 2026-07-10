@if (config('app.env') == 'production' && config('google-verification.content'))
    <meta name="google-site-verification" content="{{ config('google-verification.content') }}" />
@endif
