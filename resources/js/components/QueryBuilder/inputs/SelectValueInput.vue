<template>
    <div class="flex-1 min-w-0">
        <v-select
            v-if="isMultiSelect"
            :value="condition.value"
            :options="field.options || []"
            :reduce="option => option.value"
            label="label"
            multiple
            :placeholder="__('Select Values')"
            @input="$emit('update-value', $event)"
        />
        <v-select
            v-else
            :value="condition.value"
            :options="field.options || []"
            :reduce="option => option.value"
            label="label"
            :placeholder="__('Select Value')"
            @input="$emit('update-value', $event)"
        />
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
    },

    computed: {
        isMultiSelect() {
            return this.field?.type === 'select' && ['IN', 'NOT IN'].includes(this.condition.operator);
        }
    }
}
</script>
