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
import QueryBuilder from '../components/fieldtypes/QueryBuilder.vue';
import {
    FIELD_GROUPS,
    buildGroupedFields,
    fetchProductAttributes,
    fetchSortingOptions,
    normalizeQuery
} from '../helpers/productQueryBuilder';

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

const enabled = ref(initialEnabled);
const queryValue = ref(normalizeQuery(initialQuery));
const groupedFields = ref([]);
const sortingOptions = ref([{ label: 'Created', value: 'created_at' }]);
const saving = ref(false);
const attributesLoaded = ref(false);

const loadAttributesAndSorting = async () => {
    try {
        const [attributes, options] = await Promise.all([
            fetchProductAttributes(),
            fetchSortingOptions()
        ]);
        groupedFields.value = buildGroupedFields(attributes, FIELD_GROUPS);
        sortingOptions.value = options.length > 0 ? options : [{ label: 'Created', value: 'created_at' }];
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
    loadAttributesAndSorting();
});
</script>
