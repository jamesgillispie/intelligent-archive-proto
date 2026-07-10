const scrollbar = () => {
    window.A17 = window.A17 || {}

    const scrollbox = document.createElement('div')
    scrollbox.style.overflow = 'scroll'
    document.body.appendChild(scrollbox)

    // Compare inner and out widths of the box to determine scroll bar width
    const scrollBarWidth = scrollbox.offsetWidth - scrollbox.clientWidth

    document.body.removeChild(scrollbox)
    document.documentElement.style.setProperty(
        '--scrollbar-width',
        `${scrollBarWidth}px`
    )
    window.A17.scrollBarWidth = scrollBarWidth
}

export default scrollbar
