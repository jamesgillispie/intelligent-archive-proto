// TBD: this could part of the vitrine behaviors
import { createBehavior } from '@area17/a17-behaviors'

const triggerShare = createBehavior(
    'triggerShare',
    {
        clickHandler(event) {
            event.preventDefault()
            // https://css-tricks.com/how-to-use-the-web-share-api/

            const title = document.title
            const url = document.querySelector('link[rel=canonical]')
                ? document.querySelector('link[rel=canonical]').href
                : document.location.href

            if (navigator.share) {
                navigator
                    .share({
                        title: title,
                        url: url
                    })
                    .then(() => {
                        console.log('Thanks for sharing!')
                    })
                    .catch(console.error)
            } else {
                document.dispatchEvent(
                    new CustomEvent('Modal:openById', {
                        detail: {
                            modalId: 'share-modal'
                        }
                    })
                )
            }
        }
    },
    {
        init() {
            this.$node.addEventListener('click', this.clickHandler, false)
        },
        destroy() {
            this.$node.removeEventListener('click', this.clickHandler)
        }
    }
)

export default triggerShare
