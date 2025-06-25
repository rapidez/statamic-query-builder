<template>
    <div class="flex items-center space-x-2 flex-1">
        <input
            type="text"
            :value="getBetweenValue().min"
            class="input-text flex-1"
            :placeholder="__('Min value')"
            @input="updateMin($event.target.value)"
        >
        <span>{{ __('and') }}</span>
        <input
            type="text"
            :value="getBetweenValue().max"
            class="input-text flex-1"
            :placeholder="__('Max value')"
            @input="updateMax($event.target.value)"
        >
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

    methods: {
        getBetweenValue() {
            const value = this.condition.value;

            if (!value || typeof value === 'string') {
                return { min: '', max: '' };
            } else if (Array.isArray(value)) {
                return {
                    min: value[0] || '',
                    max: value[1] || ''
                };
            }
            return value;
        },

        updateMin(min) {
            const current = this.getBetweenValue();
            this.$emit('update-value', [min, current.max]);
        },

        updateMax(max) {
            const current = this.getBetweenValue();
            this.$emit('update-value', [current.min, max]);
        }
    }
}
</script>
