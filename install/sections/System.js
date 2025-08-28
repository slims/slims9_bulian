import Logo from '../components/Logo.js'
import SlimsTextVertical from '../components/SlimsTextVertical.js'
import Version from '../components/Version.js'
import {Token} from "../js/utils.js"

export default {
    components: {
        Logo,
        SlimsTextVertical,
        Version
    },
    data() {
        return {
            data: [],
            isPass: false,
            loading: false
        }
    },
    mounted() {
        this.doCheck()
    },
    methods: {
        doCheck() {
            this.loading = true
            fetch('./api.php', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${Token}`,
                    Accept: 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'system-requirement'
                })
            })
                .then(res => res.json())
                .then(res => {
                    this.data = res.data
                    this.isPass = res.is_pass
                    this.loading = false
                })
        }
    },
    template: `<div class="min-h-screen flex">
<div class="w-20 p-4">
    <div><logo></logo></div>
    <slims-text-vertical class="text-lg font-medium text-gray-200 pt-4"></slims-text-vertical>
    <version></version>
</div>
<div class="flex-1 bg-gray-100 py-8 px-16">
    <h1 class="text-3xl font-medium">System requirements</h1>
    <p class="text-lg text-gray-700 tracking-wide mb-4">Checking the minimum system requirements to install SLiMS</p>
    <div v-if="loading">Loading...</div>
    <div class="flex flex-wrap">
        <div v-for="d,i in data" class="pt-2 w-1/2">
            <h2 class="font-medium">{{d.title}}</h2>
            <div v-if="!d.data" class="text-gray-700">{{d.version || (d.status ? 'installed' : 'not installed')}}</div>
            <div v-html="d.data" class="text-gray-700">{{d.data}}</div>
            <div v-if="!d.status" class="text-red-500">{{d.message}}</div>
            <div class="w-1/3 border-b pb-2"></div>
        </div>
    </div>
    <button v-if="(!loading && isPass)" @click="$emit('click')" class="mt-4 rounded-full bg-blue-500 py-2 px-4 text-blue-100 hover:bg-blue-400 focus:outline-none focus:bg-blue-600">Next</button>
</div>
</div>`
}
