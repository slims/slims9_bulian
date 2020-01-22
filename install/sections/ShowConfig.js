import Logo from '../components/Logo.js'
import SlimsText from '../components/SlimsText.js'
import SlimsTextVertical from '../components/SlimsTextVertical.js'
import SlimsButton from '../components/Button.js'
import Version from '../components/Version.js'
import {Token} from "../js/utils.js";

export default {
    name: 'ShowConfig',
    components: {
        Logo,
        SlimsText,
        SlimsTextVertical,
        SlimsButton,
        Version
    },
    props: ['section'],
    data() {
        return {
            loading: false,
            isPass: null,
            message: '',
        }
    },
    computed: {
        action() {
            if (this.section === 'create-admin') return 're-install'
            if (this.section === 'select-version') return 're-upgrade'
        }
    },
    methods: {
        reRunInstall() {
            this.loading = true
            fetch('./api.php', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${Token}`,
                    Accept: 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: this.action,
                })
            })
                .then(res => res.json())
                .then(res => {
                    this.loading = false
                    this.isPass = res.status
                    this.message = res.message

                    if (!this.isPass && (res.code === 5000 || res.code === 5001)) {
                        this.$emit('notwrite')
                    } else if (this.isPass) {
                        this.$emit('success')
                    }
                })
                .catch(err => {
                    this.loading = false
                    this.isPass = false
                    this.message = err.message
                })
        }
    },
    template: `<div class="h-screen flex">
<div class="w-20 p-4">
    <div><logo></logo></div>
    <slims-text-vertical class="text-lg font-medium text-gray-200 pt-4"></slims-text-vertical>
    <version></version>
</div>
<div class="flex-1 bg-gray-100 py-8 px-16">
    <h1 class="text-3xl font-medium">Upsst... something wrong!</h1>
    <p class="text-lg text-gray-700 tracking-wide mb-4">
    Sorry, but <slims-text></slims-text> can&#8217;t write the config file.
    </p>
    <p class="text-lg text-gray-700 tracking-wide mb-4">
    You can create the <code class="text-red-500 text-sm">sysconfig.local.inc.php</code> 
    file manually base on <code class="text-red-500 text-sm">sysconfig.local.inc-sample.php</code> 
    file in the <code class="text-red-500 text-sm">config</code> folder.<br>
    Don't forget to configure database configuration with your database connection parameters/settings.
    </p>
    <p class="text-lg text-gray-700 tracking-wide mb-4">After you&#8217;ve done that, click &#8220;Run the installation&#8221;.</p>
    
    <div v-if="!isPass && message !== ''" class="rounded border bg-pink-200 border-pink-500 text-pink-500 px-4 py-2 mt-2 mb-4 md:w-1/2">
        <ul>
            <li v-if="typeof message === 'object'" class="py-2" v-for="m in message">{{m}}</li>
            <li v-if="typeof message === 'string'" class="py-2">{{message}}</li>
        </ul>
    </div>
    
    <slims-button @click="reRunInstall" :loading="loading" :disabled="loading" text="Run the installation"></slims-button>
</div>
</div>`
}
