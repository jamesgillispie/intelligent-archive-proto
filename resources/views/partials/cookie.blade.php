@if (config('tracking.cookie.id'))
    <script>
        // Add configuration for cookie consent solution
        window.axeptioEnabled = true;
        window.axeptioSettings = {
            clientId: '{{ config('tracking.cookie.id') }}',
            cookiesVersion: '{{ config('tracking.cookie.version') }}',
        };
    </script>
@endif
