export default {
    name: 'SlimsButton',
    props: {
        text: {
            type: String,
            default: ''
        },
        color: {
            type: String,
            default: 'bg-yellow-500'
        },
        loading: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        type: {
            type: String,
            default: 'button'
        }
    },
    template: `<button @click="onClick" :type="type" :disabled="disabled" :class="state" 
            class="text-white py-3 px-5 rounded-full font-bold flex justify-center items-center focus:outline-none">
            {{ title }}
            <div v-show="loading" class="lds-dual-ring ml-3"><div></div><div></div></div></button>`,
    computed: {
        title() {
            if (this.loading) return 'Please wait ...';
            return this.text
        },
        state() {
            if (this.disabled) return ['bg-gray-500', 'cursor-not-allowed'];
            return [this.color]
        }
    },
    methods: {
        onClick(e) {
            this.$emit('click', e)
        }
    }
}