import Logo from '../components/Logo.js'
import SlimsText from '../components/SlimsText.js'
import Features from '../components/Features.js'
import SlimsButton from '../components/Button.js'

export default {
    components: {
        Logo,
        SlimsText,
        Features,
        SlimsButton
    },
    template: `<section class="flex h-screen">
        <div class="w-1/2 p-4 flex flex-col">
            <div class="flex items-center">
                <div class="w-12 mr-4">
                    <logo/>
                </div>
                <slims-text class="text-2xl text-gray-200" style="letter-spacing: 0.5em"/>
            </div>
            <div class="flex-1 flex flex-col justify-center items-center text-center text-gray-200 px-12">
                <h1 class="text-2xl mb-2">Welcome to
                    <slims-text class="tracking-widest"/>
                </h1>
                <p class="mb-4">As an integrated library management system, <slims-text></slims-text> (Senayan Library Management System) offers many
                    features to assist libraries and librarians do their jobs quickly, neatly, and with style.</p>
                <slims-button @click="$emit('click')" text="Get Started"></slims-button>
            </div>
        </div>
        <div class="w-1/2 py-8">
            <div class="bg-gray-100 rounded-l-lg h-full py-4 px-16">
                <features></features>
            </div>
        </div>
    </section>`
}
