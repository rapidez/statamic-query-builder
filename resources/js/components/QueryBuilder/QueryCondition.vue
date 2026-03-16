<template>
    <div class="flex items-center space-x-4 p-3 rounded-md">
        <Select
            :model-value="condition.attribute"
            :options="fields"
            :reduce="field => field.value"
            label="label"
            class="w-1/3"
            :placeholder="__('Select Field')"
            @update:model-value="updateAttribute"
        />

        <Select
            :model-value="condition.operator"
            :options="getOperatorsForType(condition.attribute)"
            :reduce="op => op.value"
            label="label"
            class="w-1/4"
            :placeholder="__('Select Operator')"
            @update:model-value="updateOperator"
        />

        <template v-if="needsValueInput(condition.operator)">
            <component
                :is="getValueInputComponent()"
                :condition="condition"
                :field="getCurrentField()"
                @update-value="updateValue"
            />
        </template>

        <Button
            class="btn-danger"
            @click="$emit('remove-condition', conditionIndex)"
            title="Remove condition"
        >
            ×
        </Button>
    </div>
</template>

<script setup>
import { Select, Button } from '@statamic/cms/ui';
import DateValueInput from './inputs/DateValueInput.vue';
import SelectValueInput from './inputs/SelectValueInput.vue';
import BetweenValueInput from './inputs/BetweenValueInput.vue';
import DaysValueInput from './inputs/DaysValueInput.vue';
import TextValueInput from './inputs/TextValueInput.vue';

const props = defineProps({
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
});

const emit = defineEmits(['update-condition', 'remove-condition']);

const getCurrentField = () => {
    return props.fields.find(field => field.value === props.condition.attribute);
};

const isDateField = (fieldValue) => {
    const field = props.fields.find(field => field.value === fieldValue);
    return field?.type === 'date';
};

const isMultiSelectVisible = (condition) => {
    const field = props.fields.find(field => field.value === condition?.attribute);
    return field?.type === 'select' && ['IN', 'NOT IN'].includes(condition?.operator);
};

const isSingleSelectVisible = (condition) => {
    const field = props.fields.find(field => field.value === condition?.attribute);
    return field?.type === 'select' && !['IN', 'NOT IN'].includes(condition?.operator);
};

const needsValueInput = (operator) => {
    const noValueOperators = ['IS_NULL', 'IS_NOT_NULL', 'THIS_WEEK', 'THIS_MONTH', 'THIS_YEAR'];
    return !noValueOperators.includes(operator);
};

const needsBetweenInput = (operator) => {
    return ['BETWEEN', 'NOT_BETWEEN'].includes(operator);
};

const needsDaysInput = (operator) => {
    return ['LAST_X_DAYS', 'NEXT_X_DAYS'].includes(operator);
};

const getValueInputComponent = () => {
    if (isMultiSelectVisible(props.condition) || isSingleSelectVisible(props.condition)) {
        return SelectValueInput;
    } else if (needsBetweenInput(props.condition.operator)) {
        return BetweenValueInput;
    } else if (needsDaysInput(props.condition.operator)) {
        return DaysValueInput;
    } else if (isDateField(props.condition.attribute)) {
        return DateValueInput;
    } else {
        return TextValueInput;
    }
};

const getOperatorsForType = (fieldValue) => {
    const field = getCurrentField();

    if (field && field.operators) {
        return field.operators.map(operator => ({
            value: operator,
            label: getOperatorLabel(operator)
        }));
    }

    return field && props.operators[field.type] ? props.operators[field.type] : props.operators.text;
};

const getOperatorLabel = (operator) => {
    const allOperators = [
        ...props.operators.text,
        ...props.operators.select,
        ...props.operators.number,
        ...props.operators.date
    ];
    const found = allOperators.find(operatorObject => operatorObject.value === operator);

    return found ? found.label : operator;
};

const updateAttribute = (attribute) => {
    const updatedCondition = {
        ...props.condition,
        attribute,
        operator: getOperatorsForType(attribute)[0]?.value || '=',
        value: getDefaultValueForField(attribute)
    };
    emit('update-condition', props.conditionIndex, updatedCondition);
};

const updateOperator = (operator) => {
    const updatedCondition = {
        ...props.condition,
        operator,
        value: getDefaultValueForOperator(operator)
    };
    emit('update-condition', props.conditionIndex, updatedCondition);
};

const updateValue = (value) => {
    const updatedCondition = { ...props.condition, value };
    emit('update-condition', props.conditionIndex, updatedCondition);
};

const getDefaultValueForField = (fieldValue) => {
    const field = props.fields.find(f => f.value === fieldValue);
    if (field?.type === 'date') {
        return { type: 'relative', value: 'TODAY' };
    }
    return '';
};

const getDefaultValueForOperator = (operator) => {
    if (needsBetweenInput(operator)) {
        return { min: '', max: '' };
    } else if (!needsValueInput(operator)) {
        return null;
    }
    return props.condition.value;
};
</script>
