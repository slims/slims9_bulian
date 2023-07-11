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
            allVersion: [
                {value: 0, text: '-- Select Version --'},
                {value: 1, text: 'Senayan 3 - Stable 3'},
                {value: 2, text: 'Senayan 3 - Stable 4'},
                {value: 3, text: 'Senayan 3 - Stable 5'},
                {value: 4, text: 'Senayan 3 - Stable 6'},
                {value: 5, text: 'Senayan 3 - Stable 7'},
                {value: 6, text: 'Senayan 3 - Stable 8'},
                {value: 7, text: 'Senayan 3 - Stable 9'},
                {value: 8, text: 'Senayan 3 - Stable 10'},
                {value: 9, text: 'Senayan 3 - Stable 11'},
                {value: 10, text: 'Senayan 3 - Stable 12'},
                {value: 11, text: 'Senayan 3 - Stable 13'},
                {value: 12, text: 'Senayan 3 - Stable 14 | Seulanga'},
                {value: 13, text: 'Senayan 3 - Stable 15 | Matoa'},
                {value: 14, text: 'SLiMS 5 | Meranti'},
                {value: 15, text: 'SLiMS 7 | Cendana'},
                {value: 16, text: 'SLiMS 8 | Akasia'},
                {value: 17, text: 'SLiMS 8.2 | Akasia'},
                {value: 18, text: 'SLiMS 8.3 | Akasia'},
                {value: 19, text: 'SLiMS 8.3.1 | Akasia'},
                {value: 20, text: 'SLiMS 9.0.0 | Bulian'},
                {value: 21, text: 'SLiMS 9.1.0 | Bulian'},
                {value: 22, text: 'SLiMS 9.1.1 | Bulian'},
                {value: 23, text: 'SLiMS 9.2.0 | Bulian'},
                {value: 24, text: 'SLiMS 9.2.1 | Bulian'},
                {value: 25, text: 'SLiMS 9.2.2 | Bulian'},
                {value: 26, text: 'SLiMS 9.3.0 | Bulian'},
                {value: 27, text: 'SLiMS 9.3.1 | Bulian'},
                {value: 28, text: 'SLiMS 9.4.0 | Bulian'},
                {value: 29, text: 'SLiMS 9.4.1 | Bulian'},
                {value: 30, text: 'SLiMS 9.4.2 | Bulian'},
				{value: 31, text: 'SLiMS 9.5.0 | Bulian'},
                {value: 32, text: 'SLiMS 9.5.1 | Bulian'},
                {value: 33, text: 'SLiMS 9.5.2 | Bulian'},
                {value: 34, text: 'SLiMS 9.6.0 | Bulian'}
            ]
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
                    this.message = res.message
                    this.loading = false

                    if (!this.isPass && (res.code === 5000 || res.code === 5001)) {
                        this.$emit('notwrite')
                    } else if (this.isPass) {
                        this.$emit('success')
                    }
                })
                .catch((error) => {
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
        setSuggestion(engine)
        {
            if (engine === 'Aria') return engine + ' - recommended for crash safe'
            return engine
        }
    },
    mounted() {
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
        <ul>
            <li class="py-2" v-for="m in message">{{m}}</li>
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

    <slims-button @click="doUpgrade" :loading="loading" :disabled="oldVersion < 1" type="button" text="Run the installation"></slims-button>
</div>
</div>`
}
