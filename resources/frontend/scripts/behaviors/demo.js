import { createBehavior } from '@area17/a17-behaviors'

const demo = createBehavior(
    'demo',
    {
        onLoad() {
            console.log('demo loaded')
        }
    },
    {
        init() {
            console.log('demo init')
        },
        destroy() {}
    }
)

export default demo
