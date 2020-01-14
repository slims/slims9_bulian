export default {
    name: 'Features',
    data() {
        return {
            features: [
                {
                    head: 'The Power of Open Source',
                    body: 'No cost needed. Just Download.'
                },
                {
                    head: 'Bibliography',
                    body: 'Input data, faster, with peer-to-peer copy cataloguing.'
                },
                {
                    head: 'Patron',
                    body: 'Make your library card, instantly.'
                },
                {
                    head: 'Report',
                    body: 'Robust reporting. Print it or easily convert to CSV.'
                },
                {
                    head: 'Stock Opname',
                    body: 'Inventory check with style. As easy as ABC.'
                },
                {
                    head: 'Serial Control',
                    body: 'Make list of your serials subscription.'
                },
                {
                    head: 'More Features',
                    body: 'Get started, try it and have fun. You’ll find paradise ;)'
                }
            ]
        }
    },
    template: `<div class="flex flex-col justify-center h-full">
<div v-for="feature in features" class="pb-4">
    <h2 class="font-medium uppercase">{{feature.head}}</h2>
    <p class="text-sm text-gray-700">{{feature.body}}</p>
</div>
</div>`
}
