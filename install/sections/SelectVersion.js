import Logo from '../components/Logo.js'
import SlimsText from '../components/SlimsText.js'
import SlimsTextVertical from '../components/SlimsTextVertical.js'
import SlimsButton from '../components/Button.js'
import Version from '../components/Version.js'
import {Token} from "../js/utils.js";

export default {
    name: 'SelectVersion',
    components: {
        Logo,
        SlimsText,
        SlimsTextVertical,
        SlimsButton,
        Version
    },
    data() {
        return {
            oldVersion: 0,
            isPass: null,
            message: '',
            loading: false,
            engines: [],
            engine: 'MyISAM',
            allVersion: [],
            btnLabel: 'Run the installation'
        }
    },
    methods: {
        doUpgrade() {
            this.loading = true
            fetch('./api.php', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${Token}`,
                    Accept: 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'do-upgrade',
                    oldVersion: this.oldVersion,
                    engine: this.engine
                })
            })
                .then(res => res.json())
                .then(res => {
                    this.isPass = res.status

                    if (this.isPass) {
                        this.$emit('success')
                        return
                    }

                    this.message = res.message
                    this.loading = false

                    let hasPriorityError = this.message.filter((item) => item.priority_error !== null)
                    let optionalError = this.message
                                                .filter((item) => item.priority_error === null)
                                                .map((item) => item.optional_error)

                    if (!this.isPass && (hasPriorityError.length > 0 || res.code === 5000 || res.code === 5001)) {
                        this.loading = false
                        this.btnLabel = 'Re-' + this.btnLabel
                    } else if (this.message.length > 0 && hasPriorityError.length < 1) {
                        this.$emit('redirectwithmsg', optionalError)
                    }
                })
                .catch((error) => {
                    console.log(error)
                    this.isPass = false
                    this.message = [error.message]
                    this.loading = false
                });
        },
        async getEngines() {
            try {
                let request = await (await (fetch('./api.php?storeage_engines=yes'))).json()

                if (!request.status) throw request.message??'Something error'

                this.engines = request.data
            } catch (error) {
                console.log(error)
            }
        },
        async getVersionList() {
            try {
                let request = await (await (fetch('./api.php?versionlist=yes'))).json()

                if (!request.status) throw request.message??'Something error'

                this.allVersion = request.data.map(function(label, order){
                    return {value: order, text: label}
                })
            } catch (error) {
                console.log(error)
            }
        },
        setSuggestion(engine)
        {
            if (engine === 'Aria') return engine + ' - recommended for crash safe'
            return engine
        }
    },
    mounted() {
        this.getVersionList()
        this.getEngines()
    },
    template: `<div class="h-screen flex">
<div class="w-20 p-4">
    <div><logo></logo></div>
    <slims-text-vertical class="text-lg font-medium text-gray-200 pt-4"></slims-text-vertical>
    <version></version>
</div>
<div class="flex-1 bg-gray-100 py-8 px-16">
    <h1 class="text-3xl font-medium">Upgrade my previous <slims-text></slims-text><span class="text-gray-500 text-lg"><span class="pl-4 pr-2">&mdash;</span> 2 of 2</span></h1>
    <p class="text-lg text-gray-700 tracking-wide mb-4">Please follow the instructions and fill the form if required</p>
    
    <h2 class="font-medium text-lg"> Your SLiMS Version</h2>
    <p class="mb-2">Please select your current <slims-text></slims-text> version before continue.</p>
    
    <div v-if="!isPass && message !== ''" class="rounded border bg-pink-200 border-pink-500 text-pink-500 px-4 py-2 my-2 md:w-1/2">
        <h2 class="text-xl font-medium">Oops! Something error</h2>
        <p class="text-lg tracking-wide mb-4">Please fix the error(s), and Re-Run Instalation again</p>
        <ul>
            <li class="py-2" v-for="m in message">{{m.priority_error}}</li>
        </ul>
    </div>
    
    <div class="inline-block relative w-64 mb-4">
      <select v-model="oldVersion" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
        <option v-for="v in allVersion" :value="v.value">{{v.text}}</option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
      </div>
    </div>

    <h2 class="font-medium text-lg" v-if="engines.length > 0">Storage Engine</h2>
    <p class="mb-2" v-if="engines.length > 0"><slims-text></slims-text> comes with variant of database storage engine, it can improve your database performance such as crash-safe, transaction etc.</p>

    <div class="inline-block relative w-64 mb-4" v-if="engines.length > 0">
      <select v-model="engine" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
        <option v-for="engine in engines" :value="engine[0]" :title="engine[1]">{{ setSuggestion(engine[0]) }}</option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
      </div>
    </div>

    <slims-button @click="doUpgrade" :loading="loading" :disabled="oldVersion < 1" type="button" :text="btnLabel"></slims-button>
</div>
</div>`
}
