<template>
    <input
        type="text"
        :value="condition.value"
        class="input-text flex-1"
        :placeholder="__('Enter value')"
        @input="$emit('update-value', $event.target.value)"
    >
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
