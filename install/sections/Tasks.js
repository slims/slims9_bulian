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
    <p class="text-lg text-gray-700 tracking-wide mb-4">Choose your side!</p>
    
    <h1 class="font-medium text-xl">Brand new SLiMS installation</h1>
    <p class="w-2/3">You are a first time user. You have not utilise any automation system to manage your library activity. Or, you have used another automation system but you want to give SLiMS a try. Go ahead, do not hesitate to install it. Clik the button.</p>
    <button @click="$emit('click', 'install')" class="mt-2 mb-8 rounded-full bg-green-500 py-2 px-4 text-green-100 hover:bg-green-400 focus:outline-none focus:bg-green-600">Install SLiMS</button>

    <h1 class="font-medium text-xl">Upgrade my previous SLiMS version</h1>
    <p class="w-2/3">So, you have finally chose your side. And you want to feel more power by using the brand new SLiMS version. Remember, every action demands its own responsibility. Clik the button to taste more of its sweetness.</p>
    <button @click="$emit('click', 'upgrade')" class="mt-2 mb-4 rounded-full bg-blue-500 py-2 px-4 text-blue-100 hover:bg-blue-400 focus:outline-none focus:bg-blue-600">Upgrade my SLiMS</button>

</div>
</div>`
}
