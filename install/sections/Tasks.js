import Logo from '../components/Logo.js'
import SlimsTextVertical from '../components/SlimsTextVertical.js'
import Version from '../components/Version.js'

export default {
    name: 'Tasks',
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
    <h1 class="text-3xl font-medium">What do you want?</h1>
    <p class="text-lg text-gray-700 tracking-wide mb-4">Please select task below to continue</p>
    
    <h1 class="font-medium text-xl">Brand new SLiMS installation</h1>
    <p class="w-2/3">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
    <button @click="$emit('click', 'install')" class="mt-2 mb-8 rounded-full bg-green-500 py-2 px-4 text-green-100 hover:bg-green-400 focus:outline-none focus:bg-green-600">Install it</button>

    <h1 class="font-medium text-xl">Upgrade my previous SLiMS version</h1>
    <p class="w-2/3">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
    <button @click="$emit('click', 'upgrade')" class="mt-2 mb-4 rounded-full bg-blue-500 py-2 px-4 text-blue-100 hover:bg-blue-400 focus:outline-none focus:bg-blue-600">Upgrade my SLiMS</button>

</div>
</div>`
}
