<template>
    <div class="flex items-center space-x-2 flex-1">
        <input
            type="number"
            :value="condition.value"
            class="input-text flex-1"
            :placeholder="__('Number of days')"
            min="1"
            @input="$emit('update-value', parseInt($event.target.value) || 1)"
        >
        <span>{{ __('days') }}</span>
    </div>
</template>

<script>
export default {
    props: {
        condition: {
            type: Object,
            required: true
        },
        field: {
            type: Object,
            required: true
        }
    }
}
</script>
