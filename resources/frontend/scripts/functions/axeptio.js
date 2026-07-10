import {
    disableAnalyticsTracking,
    disableGoogleAds,
    grantAnalyticsTracking,
    grantGoogleAds
} from './tracking.js'

/**
 * Enabled callbacks when user accept cookies
 */
const loadAxeptio = () => {
    ;(function (d, s) {
        var t = d.getElementsByTagName(s)[0],
            e = d.createElement(s)
        e.async = true
        e.src = '//static.axept.io/sdk-slim.js'
        t.parentNode.insertBefore(e, t)
    })(document, 'script')
}

/**
 * Enabled callbacks when user accept cookies
 */
let links = []

const openCookieManagement = (event) => {
    event.preventDefault()
    window?.openAxeptioCookies()
}

const addCookiesManagementLink = () => {
    links = Array.from(
        document.querySelectorAll('[href="#cookies-management"]') || []
    )
    links.forEach((link) =>
        link.addEventListener('click', openCookieManagement)
    )
}

const init = () => {
    if (!window.axeptioEnabled) return

    window.setTimeout(() => {
        loadAxeptio()
        addCookiesManagementLink()
    }, 100)

    window._axcb = window._axcb || []
    window._axcb.push(function (axeptio) {
        axeptio.on('cookies:complete', function (choices) {
            if (choices['google_analytics']) {
                grantAnalyticsTracking()
            } else {
                disableAnalyticsTracking()
            }

            if (choices['Google_Ads']) {
                grantGoogleAds()
            } else {
                disableGoogleAds()
            }

            if (choices.clarity) {
                if (typeof window?.clarity === 'function') {
                    window?.clarity('consent')
                }
            }
        })
    })

    document.addEventListener('swup:visit:start', () => {
        links.forEach((link) =>
            link.removeEventListener('click', openCookieManagement)
        )
    })

    document.addEventListener('swup:visit:end', () => {
        addCookiesManagementLink()
    })
}

export default init
