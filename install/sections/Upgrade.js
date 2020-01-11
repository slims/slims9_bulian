import Logo from '../components/Logo.js'
import SlimsTextVertical from '../components/SlimsTextVertical.js'
import Version from '../components/Version.js'

export default {
    name: 'Install',
    components: {
        Logo,
        SlimsTextVertical,
        Version
    },
    template: `<div class="h-screen flex">
<div class="w-20 p-4">
    <div><logo></logo></div>
    <slims-text-vertical class="text-lg font-medium text-gray-200 pt-4"></slims-text-vertical>
    <version></version>
</div>
<div class="flex-1 bg-gray-100 py-8 px-16">
    <h1 class="text-3xl font-medium">Upgrade</h1>
    <p class="text-lg text-gray-700 tracking-wide mb-4">...</p>
</div>
</div>`
}
