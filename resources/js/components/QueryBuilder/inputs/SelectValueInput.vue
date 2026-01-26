<template>
    <div class="flex-1 min-w-0">
        <Select
            v-if="isMultiSelect"
            :model-value="condition.value"
            :options="field.options || []"
            :reduce="option => option.value"
            label="label"
            multiple
            :placeholder="__('Select Values')"
            @update:model-value="$emit('update-value', $event)"
        />
        <Select
            v-else
            :model-value="condition.value"
            :options="field.options || []"
            :reduce="option => option.value"
            label="label"
            :placeholder="__('Select Value')"
            @update:model-value="$emit('update-value', $event)"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Select } from '@statamic/cms/ui';

const props = defineProps({
    condition: {
        type: Object,
        required: true
    },
    field: {
        type: Object,
        required: true
    }
});

defineEmits(['update-value']);

const isMultiSelect = computed(() => {
    return props.field?.type === 'select' && ['IN', 'NOT IN'].includes(props.condition.operator);
});
</script>
