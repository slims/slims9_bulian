import Logo from '../components/Logo.js'
import SlimsText from '../components/SlimsText.js'
import SlimsTextVertical from '../components/SlimsTextVertical.js'
import SlimsButton from '../components/Button.js'
import Version from '../components/Version.js'
import {Token} from "../js/utils.js";

export default {
    name: 'Account',
    components: {
        Logo,
        SlimsText,
        SlimsTextVertical,
        SlimsButton,
        Version
    },
    data() {
        return {
            username: 'admin',
            passwd: '',
            confirmPasswd: '',
            loading: false,
            message: [],
            isPass: false,
            sampleData: false,
            engines: [],
            engine: 'MyISAM'
        }
    },
    methods: {
        submitForm() {
            if (this.passwd === this.confirmPasswd) {
                this.doInstallation()
            }
        },
        doInstallation() {
            this.loading = true
            fetch('./api.php', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${Token}`,
                    Accept: 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'do-install',
                    username: this.username,
                    passwd: this.passwd,
                    confirmPasswd: this.confirmPasswd,
                    sampleData: this.sampleData,
                    engine: this.engine
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
    template: `<div class="min-h-screen flex">
<div class="w-20 p-4">
    <div><logo></logo></div>
    <slims-text-vertical class="text-lg font-medium text-gray-200 pt-4"></slims-text-vertical>
    <version></version>
</div>
<div class="flex-1 bg-gray-100 py-8 px-16">
    <h1 class="text-3xl font-medium">Install New <slims-text></slims-text><span class="text-gray-500 text-lg"><span class="pl-4 pr-2">&mdash;</span> 2 of 2</span></h1>
    <p class="text-lg text-gray-700 tracking-wide mb-4">Please follow the instructions and fill the form if required</p>
    
    <h2 class="font-medium text-lg">Generate Sample Data</h2>
    <p class="mb-2"><slims-text></slims-text> can generate dummy data for you. Do it?</p>
    
    <div class="inline-block relative w-64 mb-4">
      <select v-model="sampleData" class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500">
        <option :value="false">No, don't do that!</option>
        <option :value="true">Yes, please.</option>
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
    
    <h2 class="font-medium text-lg">Super User profiles</h2>
    <p>Please complete the following form with Super User login and password</p>
    
    <div v-if="!isPass && message.length > 0" class="rounded border bg-pink-200 border-pink-500 text-pink-500 px-4 py-2 my-2 md:w-1/2">
        <ul>
            <li v-for="m in message">{{m}}</li>
        </ul>
    </div>
    
    <form class="w-full max-w-xl pb-4" @submit.prevent="submitForm">
      <div class="w-full mt-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1" for="grid-last-name">
            Username
          </label>
          <input required v-model="username" id="db_host" class="md:w-1/2 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="Enter username">
      </div>
      <div class="w-full mt-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1" for="grid-last-name">
            Password
          </label>
          <input required v-model="passwd" ref="db_name" id="db_name" class="md:w-1/2 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" type="password" placeholder="Enter password">
      </div>
      <div class="w-full mt-3 mb-4">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1" for="grid-last-name">
            Retype Password
          </label>
          <input required v-model="confirmPasswd" ref="db_user" id="db_user" class="md:w-1/2 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" type="password" placeholder="Retype password">
      </div>
      <slims-button :loading="loading" :disabled="loading" type="submit" text="Run the installation"></slims-button>
    </form>
    
</div>
</div>`
}