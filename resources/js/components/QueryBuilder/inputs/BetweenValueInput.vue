<template>
    <div class="flex items-center space-x-2 flex-1">
        <Input
            type="text"
            :value="getBetweenValue().min"
            class="input-text flex-1"
            :placeholder="__('Min value')"
            @input="updateMin($event.target.value)"
        />
        <span>{{ __('and') }}</span>
        <Input
            type="text"
            :value="getBetweenValue().max"
            class="input-text flex-1"
            :placeholder="__('Max value')"
            @input="updateMax($event.target.value)"
        />
    </div>
</template>

<script setup>
import { Input } from '@statamic/cms/ui';

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

const emit = defineEmits(['update-value']);

const getBetweenValue = () => {
    const value = props.condition.value;

    if (!value || typeof value === 'string') {
        return { min: '', max: '' };
    } else if (Array.isArray(value)) {
        return {
            min: value[0] || '',
            max: value[1] || ''
        };
    }
    return value;
};

const updateMin = (min) => {
    const current = getBetweenValue();
    emit('update-value', [min, current.max]);
};

const updateMax = (max) => {
    const current = getBetweenValue();
    emit('update-value', [current.min, max]);
};
</script>
