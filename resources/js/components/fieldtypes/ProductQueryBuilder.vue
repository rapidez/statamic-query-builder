<template>
    <query-builder
        :fields="attributes"
        :sort-fields="attributes"
        :default-sort-field="attributes[0].value"
        :default-limit="100"
        :show-limit="true"
        :builder-templates="templates"
        :default-builder-template="'slider'"
        v-model="value"
    />
</template>

<script>
import QueryBuilder from './QueryBuilder.vue';

export default {
    components: {
        QueryBuilder
    },

    mixins: [Fieldtype],

    data() {
        return {
            attributes: [],
            templates: [
                { label: 'Slider', value: 'slider' },
                { label: 'Listing', value: 'listing' }
            ]
        }
    },

    methods: {
        async fetchAttributes() {
            try {
                const response = await this.$axios.get('/cp/rapidez/product-attributes');
                this.attributes = response.data.map(attr => ({
                    label: attr.frontend_label ? (attr.frontend_label + (` (${attr.code})`)) : attr.code,
                    value: attr.code,
                    type: this.mapAttributeType(attr.input),
                    options: this.mapAttributeOptions(attr.options)
                }));
            } catch (error) {
                console.error('Error fetching attributes:', error);
            }
        },

        mapAttributeType(frontendInput) {
            const typeMap = {
                'text': 'text',
                'textarea': 'text',
                'select': 'select',
                'multiselect': 'select',
                'boolean': 'select',
                'price': 'number',
                'weight': 'number',
                'date': 'date',
                'datetime': 'date',
                'timestamp': 'date'
            };
            return typeMap[frontendInput] || 'text';
        },

        mapAttributeOptions(options) {
            if (!options.length) {
                return [];
            };

            if (typeof options === 'object' && !Array.isArray(options)) {
                options = Object.values(options);
            }

            return options.map(option => ({
                label: option.store_value || option.option_id,
                value: option.option_id
            }));
        }
    },

    mounted() {
        this.fetchAttributes();
    }
}
</script>
