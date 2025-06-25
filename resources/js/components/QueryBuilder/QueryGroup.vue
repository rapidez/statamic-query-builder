<template>
    <div class="border border-gray-300 rounded-lg p-4">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
            <div class="flex items-center space-x-4">
                <h3 class="text-base font-bold">{{ __('Group') }} {{ groupIndex + 1 }}</h3>
                <v-select
                    :value="group.conjunction"
                    :options="logicalOperators"
                    class="w-32"
                    @input="updateConjunction"
                />
            </div>
            <div class="flex items-center space-x-2">
                <button class="btn" @click="$emit('add-condition', groupIndex)">
                    {{ __('Add Condition') }}
                </button>
                <div class="flex items-center space-x-1">
                    <button
                        v-if="canMoveUp"
                        class="btn p-2"
                        @click="$emit('move-group-up', groupIndex)"
                        :title="__('Move group up')"
                    >
                        ↑
                    </button>
                    <button
                        v-if="canMoveDown"
                        class="btn p-2"
                        @click="$emit('move-group-down', groupIndex)"
                        :title="__('Move group down')"
                    >
                        ↓
                    </button>
                </div>
                <button
                    v-if="canRemove"
                    class="btn-danger"
                    @click="$emit('remove-group', groupIndex)"
                >
                    {{ __('Remove Group') }}
                </button>
            </div>
        </div>

        <div class="space-y-3">
            <query-condition
                v-for="(condition, conditionIndex) in group.conditions"
                :key="conditionIndex"
                :condition="condition"
                :condition-index="conditionIndex"
                :group-index="groupIndex"
                :fields="fields"
                :operators="operators"
                @update-condition="updateCondition"
                @remove-condition="removeCondition"
            />
        </div>
    </div>
</template>

<script>
import QueryCondition from './QueryCondition.vue';

export default {
    components: {
        QueryCondition
    },

    props: {
        group: {
            type: Object,
            required: true
        },
        groupIndex: {
            type: Number,
            required: true
        },
        fields: {
            type: Array,
            required: true
        },
        operators: {
            type: Object,
            required: true
        },
        canMoveUp: {
            type: Boolean,
            default: false
        },
        canMoveDown: {
            type: Boolean,
            default: false
        },
        canRemove: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            logicalOperators: ['AND', 'OR']
        }
    },

    methods: {
        updateConjunction(conjunction) {
            const updatedGroup = { ...this.group, conjunction };
            this.$emit('update-group', this.groupIndex, updatedGroup);
        },

        updateCondition(conditionIndex, condition) {
            this.$emit('update-condition', this.groupIndex, conditionIndex, condition);
        },

        removeCondition(conditionIndex) {
            this.$emit('remove-condition', this.groupIndex, conditionIndex);
        }
    }
}
</script>
