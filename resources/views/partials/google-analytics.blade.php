@if (config('tracking.ga4'))
    <!-- Google tag (gtag.js) -->
    <script>
        window.ga4ID = "{{ config('tracking.ga4') }}";
        window.adsbygoogle = window.adsbygoogle || [];
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        window.adsbygoogle.pauseAdRequests = 1;
        gtag("js", new Date());
        gtag("consent", "default", {
            ad_storage: "denied",
            analytics_storage: "denied"
        });
    </script>
    <script async defer src="https://www.googletagmanager.com/gtag/js?id={{ config('tracking.ga4') }}"></script>
@endif
