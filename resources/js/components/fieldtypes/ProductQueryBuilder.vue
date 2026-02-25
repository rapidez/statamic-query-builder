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
import QueryBuilder from './QueryBuilder.vue';
import {
    FIELD_GROUPS,
    buildGroupedFields,
    fetchProductAttributes,
    fetchSortingOptions
} from '../../helpers/productQueryBuilder';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, update } = Fieldtype.use(emit, props);
defineExpose(expose);

const groupedFields = ref([]);
const sortingOptions = ref([]);
const templates = [
    { label: 'Slider', value: 'slider' },
    { label: 'Listing', value: 'listing', hideLimit: true }
];

const loadAttributesAndSorting = async () => {
    try {
        const [attributes, options] = await Promise.all([
            fetchProductAttributes(),
            fetchSortingOptions()
        ]);
        groupedFields.value = buildGroupedFields(attributes, FIELD_GROUPS);
        sortingOptions.value = options;
    } catch (error) {
        console.error('Error fetching attributes:', error);
    }
};

onMounted(() => {
    loadAttributesAndSorting();
});
</script>
