/*
  Polyfills
  // adds inert a11y attr to elements
*/
import 'wicg-inert'

/*
  A17
*/
import { manageBehaviors } from '@area17/a17-behaviors'
import {
    focusDisplayHandler,
    focusTrap,
    ios100vhFix,
    resized,
    responsiveImageUpdate
} from '@area17/a17-helpers'
import { clearAllBodyScrollLocks } from 'body-scroll-lock-upgrade'

import * as Behaviors from './behaviors'
import * as VitrineBehaviors from './behaviors/vitrine'
import Axeptio from './functions/axeptio.js'
import Pjax from './functions/pjax'
import { init as GAInit } from './functions/tracking.js'
import scrollbar from './utils/scrollbar'

window.A17 = window.A17 || {}

const onLoadCb = () => {
    // make safari recalc image sizes
    responsiveImageUpdate()
    clearAllBodyScrollLocks()
}

document.addEventListener('DOMContentLoaded', function () {
    // adds a `--1vh` CSS variable to `:root`
    ios100vhFix()

    // watch what triggers a focus event
    focusDisplayHandler()

    // listen for focus trapping requests
    focusTrap()

    // on resize, check
    resized()

    // check scroll bars widths
    scrollbar()

    // RUN

    // Google Analytics
    GAInit()

    // Cookies
    Axeptio()

    onLoadCb()
    // expose manageBehaviors
    window.A17.behaviors = manageBehaviors
    // init behaviors!
    // TBD: We may switch to direct import instead of barrel file
    window.A17.behaviors.init({
        ...VitrineBehaviors,
        ...Behaviors
    })

    // PJAX
    Pjax({
        onContentReplaced: onLoadCb
    })
})
