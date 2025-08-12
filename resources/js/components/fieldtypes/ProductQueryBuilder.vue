<template>
    <query-builder
        :fields="groupedFields"
        :sort-fields="attributes"
        :default-sort-field="attributes[0]?.value"
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
            groupedFields: [],
            templates: [
                { label: 'Slider', value: 'slider' },
                { label: 'Listing', value: 'listing' }
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
                    options: this.mapAttributeOptions(attr.options)
                }));

                this.buildGroupedFields();
            } catch (error) {
                console.error('Error fetching attributes:', error);
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
                value: option.option_id
            }));
        }
    },

    mounted() {
        this.fetchAttributes();
    }
}
</script>
