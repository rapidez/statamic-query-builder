<template>
    <div>
    <QueryBuilder
        :fields="groupedFields"
        :sort-fields="sortingOptions"
        :default-sort-field="sortingOptions[0]?.value"
        :default-limit="100"
        :show-limit="true"
        :builder-templates="templates"
        :default-builder-template="'slider'"
        :value="value"
        @update:model-value="update"
    ></QueryBuilder>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Fieldtype } from '@statamic/cms';
import axios from 'axios';
import QueryBuilder from './QueryBuilder.vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);
defineExpose(expose);

const attributes = ref([]);
const groupedFields = ref([]);
const sortingOptions = ref([]);
const templates = [
    { label: 'Slider', value: 'slider' },
    { label: 'Listing', value: 'listing', hideLimit: true }
];
const fieldGroups = [
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
];

const fetchAttributes = async () => {
    try {
        const response = await axios.get('/cp/rapidez/product-attributes');
        attributes.value = response.data.map(attr => ({
            label: attr.frontend_label ? (attr.frontend_label + (` (${attr.attribute_code})`)) : attr.attribute_code,
            value: `attribute.${attr.attribute_code}`,
            type: mapAttributeType(attr.input),
            options: mapAttributeOptions(attr.attribute_options)
        }));

        buildGroupedFields();
    } catch (error) {
        console.error('Error fetching attributes:', error);
    }
};

const fetchSortingOptions = async () => {
    try {
        const response = await axios.get('/cp/rapidez/sorting-options');
        const options = Array.isArray(response.data) ? response.data : [];

        if (!options.length) {
            return;
        }

        sortingOptions.value = options.filter(option => option && option.value);
    } catch (e) {
        console.error('Error fetching sorting options:', e);
        sortingOptions.value = [
            { label: 'Newest', value: 'created_at' },
            { label: 'Oldest', value: 'created_at' },
        ];
    }
};

const buildGroupedFields = () => {
    groupedFields.value = fieldGroups.map(group => {
        if (group.key === 'attribute') {
            return {
                label: group.label,
                options: attributes.value
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
};

const mapAttributeType = (frontendInput) => {
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
};

const mapAttributeOptions = (options) => {
    if (!options.length) {
        return [];
    }

    if (typeof options === 'object' && !Array.isArray(options)) {
        options = Object.values(options);
    }

    return options.map(option => ({
        label: option.store_value || option.option_id,
        value: option.store_value || option.option_id
    }));
};

onMounted(() => {
    fetchAttributes();
    fetchSortingOptions();
});
</script>
