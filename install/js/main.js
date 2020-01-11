import Welcome from '../sections/Welcome.js'
import System from '../sections/System.js'
import Tasks from '../sections/Tasks.js'
import Install from '../sections/Install.js'
import Upgrade from '../sections/Upgrade.js'
import Account from '../sections/Account.js'
import ShowConfig from '../sections/ShowConfig.js'
import Success from '../sections/Success.js'

new Vue({
    el: '#app',
    components: {
        Welcome,
        System,
        Tasks,
        Install,
        Upgrade,
        Account,
        ShowConfig,
        Success
    },
    data() {
        return {
            section: 'welcome'
        }
    },
    methods: {
        selectTask(task) {
            this.section = task
        }
    }
})