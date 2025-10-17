<template>
    <query-builder
        :fields="groupedFields"
        :sort-fields="sortingOptions"
        :default-sort-field="sortingOptions[0]?.value"
        :default-limit="100"
        :show-limit="true"
        :builder-templates="templates"
        :default-builder-template="'slider'"
        :value="value"
        @input="$emit('input', $event)"
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
            groupedFields: [],
            sortingOptions: [],
            templates: [
                { label: 'Slider', value: 'slider' },
                { label: 'Listing', value: 'listing', hideLimit: true }
            ],
            fieldGroups: [
                {
                    key: 'attribute',
                    label: 'Attributes',
                    prefix: 'attribute.'
                },
                {
                    key: 'stock',
                    label: 'Stock',
                    prefix: '',
                    fields: [
                        {
                            label: 'Stock Status',
                            value: 'stock_status',
                            type: 'select',
                            operators: ['=', '!='],
                            options: [
                                { label: 'In Stock', value: 'in_stock' },
                                { label: 'Out of Stock', value: 'out_of_stock' }
                            ]
                        }
                    ]
                }
            ]
        }
    },

    methods: {
        async fetchAttributes() {
            try {
                const response = await this.$axios.get('/cp/rapidez/product-attributes');
                this.attributes = response.data.map(attr => ({
                    label: attr.frontend_label ? (attr.frontend_label + (` (${attr.code})`)) : attr.code,
                    value: `attribute.${attr.code}`,
                    type: this.mapAttributeType(attr.input),
                    options: this.mapAttributeOptions(attr.attribute_options)
                }));

                this.buildGroupedFields();
            } catch (error) {
                console.error('Error fetching attributes:', error);
            }
        },

        async fetchSortingOptions() {
            try {
                const response = await this.$axios.get('/cp/rapidez/sorting-options');
                const options = Array.isArray(response.data) ? response.data : [];

                if (!options.length) {
                    return;
                }

                this.sortingOptions = options.filter(option => option && option.value);
            } catch (e) {
                console.error('Error fetching sorting options:', e);
                this.sortingOptions = [
                    { label: 'Newest', value: 'created_at' },
                    { label: 'Oldest', value: 'created_at' },
                ];
            }
        },

        buildGroupedFields() {
            this.groupedFields = this.fieldGroups.map(group => {
                if (group.key === 'attribute') {
                    return {
                        label: group.label,
                        options: this.attributes
                    };
                }

                return {
                    label: group.label,
                    options: group.fields.map(field => ({
                        ...field,
                        value: group.prefix ? `${group.prefix}${field.value}` : field.value
                    }))
                };
            });
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
                value: option.store_value || option.option_id
            }));
        }
    },

    mounted() {
        this.fetchAttributes();
        this.fetchSortingOptions();
    }
}
</script>
