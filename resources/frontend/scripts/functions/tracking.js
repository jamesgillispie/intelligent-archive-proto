export const gtag = function () {
    window.dataLayer?.push(arguments)
}
export const init = () => {
    window.dataLayer = window.dataLayer || []

    gtag('js', new Date())

    if (window.ga4ID) {
        gtag('config', window.ga4ID)
    }

    gtag('set', 'url_passthrough', true)
}

export const grantAnalyticsTracking = () => {
    gtag('consent', 'update', {
        analytics_storage: 'granted'
    })
    document.dispatchEvent(new CustomEvent('analytics:enabled'))
}

export const disableAnalyticsTracking = () => {
    gtag('consent', 'update', {
        analytics_storage: 'denied'
    })
}

export const grantGoogleAds = () => {
    gtag('consent', 'update', {
        ad_storage: 'granted'
    })
}

export const disableGoogleAds = () => {
    gtag('consent', 'update', {
        ad_storage: 'denied'
    })
}
