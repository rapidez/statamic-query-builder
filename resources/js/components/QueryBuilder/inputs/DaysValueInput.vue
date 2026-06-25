<template>
    <div class="flex items-center space-x-2 flex-1">
        <Input
            type="number"
            :value="condition.value"
            class="input-text flex-1"
            :placeholder="__('Number of days')"
            min="1"
            @input="$emit('update-value', parseInt($event.target.value) || 1)"
        />
        <span>{{ __('days') }}</span>
    </div>
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
