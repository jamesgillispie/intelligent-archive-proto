import { createBehavior } from '@area17/a17-behaviors'

const scrollToActiveNav = createBehavior(
    'scrollToActiveNav',
    {},
    {
        init() {
            this.$active = this.$node.querySelector('[data-active]')

            if (this.$active) {
                this.$active.scrollIntoView({
                    behavior: 'instant',
                    inline: 'start'
                })
            }
        },
        destroy() {}
    }
)

export default scrollToActiveNav
