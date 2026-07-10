import SwupA11yPlugin from '@swup/a11y-plugin'
import SwupBodyClassPlugin from '@swup/body-class-plugin'
import SwupHeadPlugin from '@swup/head-plugin'
import SwupPreloadPlugin from '@swup/preload-plugin'
import SwupScriptsPlugin from '@swup/scripts-plugin'
import Swup from 'swup'

let swupInstance = null

// Valid links for swup navigation
const linkInclude = [
    `a[href^="${window.location.origin}"]`,
    `a[href^="/"]`,
    `a[href^="#"]`
]
// Links to skip for swup navigation
const linkExclude = [
    '[download]',
    '[target="_blank"]',
    '[data-no-swup]',
    `[href$=".pdf"]`
]

const exclude = linkExclude.map((selector) => `:not(${selector})`).join('')
const linkSelector = linkInclude
    .map((include) => `${include}${exclude}`)
    .join(',')

const init = ({ onContentReplaced = () => {} }) => {
    swupInstance = new Swup({
        containers: ['#pjax-wrapper'],
        animateHistoryBrowsing: false,
        animationSelector: '[class*="transition-page"]',
        linkSelector: linkSelector,
        plugins: [
            new SwupPreloadPlugin({
                preloadInitialPage: false
            }),
            new SwupA11yPlugin(),
            new SwupHeadPlugin({
                persistAssets: true,
                // Keep axeptio and other third partiy CSS injected
                persistTags: 'style'
            }),
            new SwupBodyClassPlugin(),
            new SwupScriptsPlugin({
                head: false,
                body: true
            })
        ]
    })

    swupInstance.hooks.on('content:replace', () => {
        if (typeof onContentReplaced === 'function') {
            onContentReplaced()
        }
    })

    swupInstance.hooks.on('link:click', () => {
        // close modals - after a slight delay
        window.setTimeout(() => {
            document.dispatchEvent(new CustomEvent('Modal:closeAll'))
        }, 200)
    })

    return swupInstance
}

export const navigate = async (
    url,
    opts = { force: false, method: 'GET', data: {} }
) => {
    if (swupInstance instanceof Swup && !opts.force) {
        await swupInstance.cache.delete(url)
        await swupInstance.preload(url)
        const loadOpts = {
            url,
            method: opts.method || 'GET'
        }
        if (opts.method === 'POST') {
            loadOpts.data = opts.data || {}
        }
        swupInstance.navigate(url, loadOpts)
    } else if ((opts.method || 'GET') === 'GET') {
        window.location.assign(url)
    }
}

export default init
