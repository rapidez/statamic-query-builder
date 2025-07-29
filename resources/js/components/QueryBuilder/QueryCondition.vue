<template>
    <div class="flex items-center space-x-4 p-3 rounded-md">
        <v-select
            :value="condition.attribute"
            :options="fields"
            :reduce="field => field.value"
            label="label"
            class="w-1/3"
            :placeholder="__('Select Field')"
            @input="updateAttribute"
        />

        <v-select
            :value="condition.operator"
            :options="getOperatorsForType(condition.attribute)"
            :reduce="op => op.value"
            label="label"
            class="w-1/4"
            :placeholder="__('Select Operator')"
            @input="updateOperator"
        />

        <template v-if="needsValueInput(condition.operator)">
            <component
                :is="getValueInputComponent()"
                :condition="condition"
                :field="getCurrentField()"
                @update-value="updateValue"
            />
        </template>

        <button
            class="btn-danger"
            @click="$emit('remove-condition', conditionIndex)"
            title="Remove condition"
        >
            ×
        </button>
    </div>
</template>

<script>
import DateValueInput from './inputs/DateValueInput.vue';
import SelectValueInput from './inputs/SelectValueInput.vue';
import BetweenValueInput from './inputs/BetweenValueInput.vue';
import DaysValueInput from './inputs/DaysValueInput.vue';
import TextValueInput from './inputs/TextValueInput.vue';
import FieldHelpers from '../../mixins/FieldHelpers.js';

export default {
    components: {
        DateValueInput,
        SelectValueInput,
        BetweenValueInput,
        DaysValueInput,
        TextValueInput
    },

    mixins: [FieldHelpers],

    props: {
        condition: {
            type: Object,
            required: true
        },
        conditionIndex: {
            type: Number,
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
        }
    },

    methods: {
        getValueInputComponent() {
            if (this.isMultiSelectVisible(this.condition) || this.isSingleSelectVisible(this.condition)) {
                return 'SelectValueInput';
            } else if (this.needsBetweenInput(this.condition.operator)) {
                return 'BetweenValueInput';
            } else if (this.needsDaysInput(this.condition.operator)) {
                return 'DaysValueInput';
            } else if (this.isDateField(this.condition.attribute)) {
                return 'DateValueInput';
            } else {
                return 'TextValueInput';
            }
        },

        getCurrentField() {
            return this.fields.find(field => field.value === this.condition.attribute);
        },

        getOperatorsForType(fieldValue) {
            const field = this.getCurrentField();

            if (field && field.operators) {
                return field.operators.map(operator => ({
                    value: operator,
                    label: this.getOperatorLabel(operator)
                }));
            }

            if (field) {
                return this.operators[field.type] || this.operators.text;
            }

            return this.operators.text;
        },

        getOperatorLabel(operator) {
            const allOperators = [
                ...this.operators.text,
                ...this.operators.select,
                ...this.operators.number,
                ...this.operators.date
            ];

            const found = allOperators.find(operatorObject => operatorObject.value === operator);
            return found ? found.label : operator;
        },

        updateAttribute(attribute) {
            const updatedCondition = {
                ...this.condition,
                attribute,
                operator: this.getOperatorsForType(attribute)[0]?.value || '=',
                value: this.getDefaultValueForField(attribute)
            };
            this.$emit('update-condition', this.conditionIndex, updatedCondition);
        },

        updateOperator(operator) {
            const updatedCondition = {
                ...this.condition,
                operator,
                value: this.getDefaultValueForOperator(operator)
            };
            this.$emit('update-condition', this.conditionIndex, updatedCondition);
        },

        updateValue(value) {
            const updatedCondition = { ...this.condition, value };
            this.$emit('update-condition', this.conditionIndex, updatedCondition);
        },

        getDefaultValueForField(fieldValue) {
            const field = this.fields.find(f => f.value === fieldValue);
            if (field?.type === 'date') {
                return { type: 'relative', value: 'TODAY' };
            }
            return '';
        },

        getDefaultValueForOperator(operator) {
            if (this.needsBetweenInput(operator)) {
                return { min: '', max: '' };
            } else if (!this.needsValueInput(operator)) {
                return null;
            }
            return this.condition.value;
        }
    }
}
</script>
