<template>
    <div>
        <Head :title="__('Rapidez - Default Query')" />
        <div class="max-w-4xl mx-auto py-6">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ __('Default Query Settings') }}</h1>
                <div class="flex items-center gap-4">
                    <Field>
                        <Checkbox
                            id="enabled"
                            :label="__('Enable default query')"
                            v-model="enabled"
                        />
                    </Field>
                    <Button
                        class="btn-primary"
                        :disabled="saving"
                        @click="save"
                    >
                        {{ saving ? __('Saving...') : __('Save') }}
                    </Button>
                </div>
            </div>

            <div v-if="!attributesLoaded" class="py-8 text-center text-gray-500">
                {{ __('Loading attributes...') }}
            </div>

            <div v-else class="space-y-6">
                <QueryBuilder
                    v-if="groupedFields.length > 0"
                    :fields="groupedFields"
                    :sort-fields="sortingOptions"
                    :default-sort-field="sortingOptions[0]?.value"
                    :default-limit="100"
                    :show-limit="false"
                    :builder-templates="[]"
                    :show-use-default-query-toggle="false"
                    :value="queryValue"
                    @update:model-value="handleQueryUpdate"
                />
                <div v-else class="py-8 text-center text-gray-500">
                    {{ __('No attributes available. Configure product attributes first.') }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@statamic/cms/inertia';
import { Field, Checkbox, Button } from '@statamic/cms/ui';
import axios from 'axios';
import QueryBuilder from '../components/fieldtypes/QueryBuilder.vue';

const { enabled: initialEnabled, query: initialQuery, saveUrl } = defineProps({
    enabled: {
        type: Boolean,
        default: true
    },
    query: {
        type: Object,
        default: () => ({ groups: [], globalConjunction: 'AND' })
    },
    saveUrl: {
        type: String,
        required: true
    }
});

const normalizeQuery = (query) => {
    if (!query || typeof query !== 'object') {
        return { groups: [{ conjunction: 'AND', conditions: [] }], globalConjunction: 'AND' };
    }
    const groups = Array.isArray(query.groups) && query.groups.length > 0
        ? query.groups
        : [{ conjunction: 'AND', conditions: [] }];
    return {
        groups,
        globalConjunction: query.globalConjunction || 'AND'
    };
};

const enabled = ref(initialEnabled);
const queryValue = ref(normalizeQuery(initialQuery));
const attributes = ref([]);
const groupedFields = ref([]);
const sortingOptions = ref([{ label: 'Created', value: 'created_at' }]);
const saving = ref(false);
const attributesLoaded = ref(false);

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
    if (!options || !options.length) {
        return [];
    }
    const opts = typeof options === 'object' && !Array.isArray(options) ? Object.values(options) : options;
    return opts.map((option) => ({
        label: option.store_value || option.option_id,
        value: option.store_value || option.option_id
    }));
};

const buildGroupedFields = () => {
    groupedFields.value = fieldGroups.map((group) => {
        if (group.key === 'attribute') {
            return {
                label: group.label,
                options: attributes.value
            };
        }
        return {
            label: group.label,
            options: group.fields.map((field) => ({
                ...field,
                value: group.prefix ? `${group.prefix}${field.value}` : field.value
            }))
        };
    });
};

const fetchAttributes = async () => {
    try {
        const response = await axios.get('/cp/rapidez/product-attributes');
        attributes.value = response.data.map((attr) => ({
            label: attr.frontend_label ? (attr.frontend_label + ` (${attr.attribute_code})`) : attr.attribute_code,
            value: `attribute.${attr.attribute_code}`,
            type: mapAttributeType(attr.input),
            options: mapAttributeOptions(attr.attribute_options)
        }));
        buildGroupedFields();
    } catch (error) {
        console.error('Error fetching attributes:', error);
    } finally {
        attributesLoaded.value = true;
    }
};

const handleQueryUpdate = (payload) => {
    queryValue.value = {
        groups: payload.groups || [],
        globalConjunction: payload.globalConjunction || 'AND'
    };
};

const save = () => {
    saving.value = true;
    router.post(saveUrl, {
        enabled: enabled.value,
        query: {
            groups: queryValue.value.groups,
            globalConjunction: queryValue.value.globalConjunction
        }
    }, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        }
    });
};

onMounted(() => {
    fetchAttributes();
});
</script>
