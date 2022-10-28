import Logo from '../components/Logo.js'
import SlimsText from '../components/SlimsText.js'
import SlimsTextVertical from '../components/SlimsTextVertical.js'
import Version from '../components/Version.js'
import {Token} from "../js/utils.js";

export default {
    name: 'Install',
    components: {
        Logo,
        SlimsText,
        SlimsTextVertical,
        Version
    },
    data() {
        return {
            host: 'localhost',
            port: '3306',
            name: '',
            user: '',
            pass: '',
            isPass: null,
            field: '',
            message: ''
        }
    },
    methods: {
        testConnection() {
            fetch('./api.php', {
                method: 'POST',
                headers: {
                    Authorization: `Bearer ${Token}`,
                    Accept: 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'test-connection',
                    host: this.host,
                    port: this.port,
                    name: this.name,
                    user: this.user,
                    pass: this.pass
                })
            })
                .then(res => res.json())
                .then(res => {
                    this.isPass = res.status
                    this.message = res.message
                    this.field = res.field
                    switch (this.field) {
                        case "name":
                            this.$refs.db_name.focus()
                            break;
                        case "user":
                            this.$refs.db_user.focus()
                            break;
                    }
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
    <h1 class="text-3xl font-medium">Install New <slims-text></slims-text><span class="text-gray-500 text-lg"><span class="pl-4 pr-2">&mdash;</span> 1 of 2</span></h1>
    <p class="text-lg text-gray-700 tracking-wide mb-4">Please follow the instructions and fill the form if required</p>
    
    <h2 class="font-medium text-lg">Database information</h2>
    <p>Please complete the following form with your database connection parameters/settings</p>
    
    <div v-if="!isPass && message !== ''" class="rounded border bg-pink-200 border-pink-500 text-pink-500 px-4 py-2 my-2 md:w-1/2">{{message}}</div>
    
    <form class="w-full max-w-xl" @submit.prevent="testConnection">
      <div class="w-full mt-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1" for="grid-last-name">
            Database host
          </label>
          <input required v-model="host" id="db_host" class="md:w-1/2 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="Enter host">
      </div>
      <div class="w-full mt-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1" for="grid-last-name">
            Database port
          </label>
          <input required v-model="port" id="db_port" class="md:w-1/2 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="Enter port">
      </div> 
      <div class="w-full mt-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1" for="grid-last-name">
            Database name
          </label>
          <input required v-model="name" ref="db_name" id="db_name" class="md:w-1/2 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="Enter database name">
          <p class="text-xs italic mt-1">Notes: If this database name not exist, SLiMS will try to create it for you. Make sure you have this privilege.</p>
      </div>
      <div class="w-full mt-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1" for="grid-last-name">
            Database username
          </label>
          <input required v-model="user" ref="db_user" id="db_user" class="md:w-1/2 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" type="text" placeholder="Enter username">
      </div>
      <div class="w-full mt-3">
          <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-1" for="grid-last-name">
            Database password
          </label>
          <input v-model="pass" id="db_pass" class="md:w-1/2 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500" type="password" placeholder="Enter password">
      </div>
      
      <button v-if="!isPass" type="submit" class="mt-4 mb-4 rounded-full bg-gray-500 py-2 px-4 text-gray-100 hover:bg-gray-700 focus:outline-none focus:bg-gray-600">Test Connection</button>
      <button v-if="isPass" type="button" @click="$emit('next')" class="mt-4 mb-4 rounded-full bg-green-500 py-2 px-4 text-green-100 hover:bg-green-700 focus:outline-none focus:bg-green-600">Connection OK. Next</button>
    </form>
</div>
</div>`
}
