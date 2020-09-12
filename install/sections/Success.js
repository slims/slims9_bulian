import Logo from '../components/Logo.js'
import SlimsText from '../components/SlimsText.js'
import SlimsTextVertical from '../components/SlimsTextVertical.js'
import Version from '../components/Version.js'

export default {
    name: 'Success',
    components: {
        Logo,
        SlimsText,
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
    <h1 class="text-3xl font-medium">New <slims-text></slims-text> successful installed.</h1>
    <p class="text-lg text-gray-700 tracking-wide mb-4">Congratulation, now you have <slims-text></slims-text> in your machine.</p>
    <p class="rounded border bg-pink-100 border-pink-500 text-pink-500 px-4 py-2 mt-2 mb-8 text-lg">
    Folder <code class="font-bold px-2">install</code> in your <slims-text></slims-text> is already exist. For security
                        reason please rename or remove it from your machine.</p>
                        
    <div class="w-2/3">
        <h1 class="text-2xl font-medium">Support Us</h1>
        <p class="mb-4 mt-1 text-grey-700 leading-normal">
            Support <slims-text></slims-text> development and become part of its history. We appreciate every donation to
            support us in any way. If you willing to make donation, please follow this link:
            <a class="text-blue-500 whitespace-no-wrap" href="https://slims.web.id/web/pages/support-us/" target="_blank">https://slims.web.id/web/pages/support-us/</a>
        </p>
        <p>List of individuals and or institutions who have made donations provided at <code>supports.txt</code></p>
        <p>Your donation means a lot for SLiMS development ahead</p>

        <div class="flex">
            <a href="../index.php" class="text-white bg-green-500 py-3 px-8 rounded-full font-bold mt-4 no-underline">Go to My SLiMS</a>
        </div>
    </div>

</div>
</div>`
}
	
