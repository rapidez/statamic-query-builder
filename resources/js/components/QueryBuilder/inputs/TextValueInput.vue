<template>
    <Input
        type="text"
        :value="condition.value"
        class="input-text flex-1"
        :placeholder="__('Enter value')"
        @input="$emit('update-value', $event.target.value)"
    />
</template>

<script setup>
import { Input } from '@statamic/cms/ui';

defineProps({
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
</script>
