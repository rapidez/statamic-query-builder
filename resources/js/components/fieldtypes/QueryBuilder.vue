<template>
    <div class="query-builder max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center justify-between w-full space-x-4">
                <div class="flex items-center space-x-2" v-if="showLimit">
                    <label for="limit" class="text-sm">{{ __('Limit results') }}</label>
                    <input
                        type="number"
                        id="limit"
                        v-model="limit"
                        class="input-text w-24"
                        min="1"
                        @input="updateValue"
                    >
                </div>
                <button class="btn-primary" @click="addGroup">{{ __('Add Group') }}</button>
            </div>
        </div>

        <div class="space-y-6">
            <div v-for="(group, groupIndex) in groups" :key="groupIndex" class="border border-gray-300 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <h3 class="text-base font-bold">{{ __('Group') }} {{ groupIndex + 1 }}</h3>
                        <v-select
                            v-model="group.conjunction"
                            :options="logicalOperators"
                            class="w-32"
                            @input="updateValue"
                        />
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="btn" @click="addConditionToGroup(groupIndex)">{{ __('Add Condition') }}</button>
                        <div class="flex items-center space-x-1">
                            <button
                                v-if="groupIndex > 0"
                                class="btn p-2"
                                @click="moveGroupUp(groupIndex)"
                                :title="__('Move group up')"
                            >
                                ↑
                            </button>
                            <button
                                v-if="groupIndex < groups.length - 1"
                                class="btn p-2"
                                @click="moveGroupDown(groupIndex)"
                                :title="__('Move group down')"
                            >
                                ↓
                            </button>
                        </div>
                        <button class="btn-danger" @click="removeGroup(groupIndex)">{{ __('Remove Group') }}</button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-for="(condition, conditionIndex) in group.conditions" :key="conditionIndex"
                         class="flex items-center space-x-4 p-3 rounded-md">
                        <v-select
                            v-model="condition.attribute"
                            :options="fields"
                            :reduce="field => field.value"
                            label="label"
                            class="w-1/3"
                            :placeholder="__('Select Field')"
                            @input="updateCondition(groupIndex, conditionIndex)"
                        />

                        <v-select
                            v-model="condition.operator"
                            :options="getOperatorsForType(condition.attribute)"
                            :reduce="op => op.value"
                            label="label"
                            class="w-1/4"
                            :placeholder="__('Select Operator')"
                            @input="updateCondition(groupIndex, conditionIndex)"
                        />

                        <template v-if="needsValueInput(condition.operator)">
                            <template v-if="isMultiSelectVisible(condition)">
                                <v-select
                                    v-model="condition.value"
                                    :options="getFieldOptions(condition.attribute)"
                                    :reduce="option => option.value"
                                    label="label"
                                    multiple
                                    class="flex-1 min-w-0"
                                    :placeholder="__('Select Values')"
                                    @input="updateCondition(groupIndex, conditionIndex)"
                                />
                            </template>
                            <template v-else-if="isSingleSelectVisible(condition)">
                                <v-select
                                    v-model="condition.value"
                                    :options="getFieldOptions(condition.attribute)"
                                    :reduce="option => option.value"
                                    label="label"
                                    class="flex-1 min-w-0"
                                    :placeholder="__('Select Value')"
                                    @input="updateCondition(groupIndex, conditionIndex)"
                                />
                            </template>
                            <template v-else-if="needsBetweenInput(condition.operator)">
                                <div class="flex items-center space-x-2 flex-1">
                                    <input
                                        type="text"
                                        v-model="getBetweenValue(condition).min"
                                        class="input-text flex-1"
                                        :placeholder="__('Min value')"
                                        @input="updateBetweenValue(groupIndex, conditionIndex)"
                                    >
                                    <span>and</span>
                                    <input
                                        type="text"
                                        v-model="getBetweenValue(condition).max"
                                        class="input-text flex-1"
                                        :placeholder="__('Max value')"
                                        @input="updateBetweenValue(groupIndex, conditionIndex)"
                                    >
                                </div>
                            </template>
                            <template v-else-if="needsDaysInput(condition.operator)">
                                <div class="flex items-center space-x-2 flex-1">
                                    <input
                                        type="number"
                                        v-model="condition.value"
                                        class="input-text flex-1"
                                        :placeholder="__('Number of days')"
                                        min="1"
                                        @input="updateCondition(groupIndex, conditionIndex)"
                                    >
                                    <span>days</span>
                                </div>
                            </template>

                            <template v-else-if="isDateField(condition.attribute)">
                                <div class="flex items-center space-x-2 flex-1">
                                    <v-select
                                        :value="getDateValue(condition).selectedOption"
                                        :options="getDateValueOptions()"
                                        :reduce="option => option.value"
                                        label="label"
                                        class="flex-1"
                                        :placeholder="__('Select date option')"
                                        @input="onDateOptionChange(groupIndex, conditionIndex, $event)"
                                    />

                                    <template v-if="needsOffsetInput(getDateValue(condition).selectedOption)">
                                        <input
                                            type="number"
                                            :value="getDateValue(condition).offset"
                                            class="input-text w-20"
                                            :placeholder="1"
                                            min="1"
                                            @input="updateDateOffset(groupIndex, conditionIndex, $event.target.value)"
                                        >
                                        <span class="text-sm font-medium text-gray-600">
                                            {{ getOffsetLabel(getDateValue(condition).selectedOption) }}
                                        </span>
                                    </template>

                                    <input
                                        v-if="getDateValue(condition).selectedOption === 'OTHER'"
                                        type="date"
                                        :value="getDateValue(condition).manualDate"
                                        class="input-text flex-1"
                                        @input="updateManualDate(groupIndex, conditionIndex, $event.target.value)"
                                    >
                                </div>
                            </template>
                            <template v-else>
                                <input
                                    type="text"
                                    v-model="condition.value"
                                    class="input-text flex-1"
                                    :placeholder="__('Enter value')"
                                    @input="updateCondition(groupIndex, conditionIndex)"
                                >
                            </template>
                        </template>

                        <button
                            class="btn-danger"
                            @click="removeCondition(groupIndex, conditionIndex)"
                            title="Remove condition"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="!groups.length" class="text-center py-8 bg-gray-50 rounded-lg">
                <p class="text-gray-500 mb-4">{{ __('No groups added yet. Click "Add Group" to start building your query.') }}</p>
            </div>
        </div>

        <div v-if="groups.length > 1" class="mt-6 pt-4 border-t border-gray-200">
            <div class="flex items-center space-x-4">
                <label class="font-bold">{{ __('Combine Groups with:') }}</label>
                <v-select
                    v-model="globalConjunction"
                    :options="logicalOperators"
                    class="w-32"
                    @input="updateValue"
                />
            </div>
        </div>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    props: {
        fields: {
            type: Array,
            required: true,
            validator: (value) => {
                return value.every(field =>
                    'label' in field &&
                    'value' in field &&
                    'type' in field
                );
            }
        },
        operators: {
            type: Object,
            default: () => ({
                text: [
                    { value: '=', label: __('Is exactly') },
                    { value: '!=', label: __('Is not') },
                    { value: 'LIKE', label: __('Contains') },
                    { value: 'NOT LIKE', label: __('Does not contain') },
                    { value: 'STARTS_WITH', label: __('Starts with') },
                    { value: 'ENDS_WITH', label: __('Ends with') },
                    { value: 'IS_NULL', label: __('Is empty') },
                    { value: 'IS_NOT_NULL', label: __('Is not empty') }
                ],
                select: [
                    { value: '=', label: __('Is') },
                    { value: '!=', label: __('Is not') },
                    { value: 'IN', label: __('Is any of') },
                    { value: 'NOT IN', label: __('Is none of') },
                    { value: 'IS_NULL', label: __('Nothing selected') },
                    { value: 'IS_NOT_NULL', label: __('Has selection') }
                ],
                number: [
                    { value: '=', label: __('Equals') },
                    { value: '!=', label: __('Does not equal') },
                    { value: '>', label: __('Greater than') },
                    { value: '<', label: __('Less than') },
                    { value: '>=', label: __('Greater than or equal to') },
                    { value: '<=', label: __('Less than or equal to') },
                    { value: 'BETWEEN', label: __('Is between') },
                    { value: 'NOT_BETWEEN', label: __('Is not between') },
                    { value: 'IS_NULL', label: __('Is empty') },
                    { value: 'IS_NOT_NULL', label: __('Is not empty') }
                ],
                date: [
                    { value: '=', label: __('Is on') },
                    { value: '!=', label: __('Is not on') },
                    { value: '>', label: __('Is after') },
                    { value: '<', label: __('Is before') },
                    { value: '>=', label: __('Is on or after') },
                    { value: '<=', label: __('Is on or before') },
                    { value: 'BETWEEN', label: __('Is between') },
                    { value: 'NOT_BETWEEN', label: __('Is not between') },
                    { value: 'LAST_X_DAYS', label: __('In the last days') },
                    { value: 'NEXT_X_DAYS', label: __('In the next days') },
                    { value: 'THIS_WEEK', label: __('This week') },
                    { value: 'THIS_MONTH', label: __('This month') },
                    { value: 'THIS_YEAR', label: __('This year') },
                    { value: 'IS_NULL', label: __('No date set') },
                    { value: 'IS_NOT_NULL', label: __('Has date') }
                ]
            })
        },
        defaultLimit: {
            type: Number,
            default: 100
        },
        showLimit: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            groups: [],
            limit: this.defaultLimit,
            globalConjunction: 'AND',
            logicalOperators: ['AND', 'OR']
        }
    },

    methods: {
        initializeGroups() {
            if (this.value?.groups?.length) {
                return this.value.groups;
            }
            return [{
                conjunction: 'AND',
                conditions: []
            }];
        },

        addGroup() {
            this.groups.push({
                conjunction: 'AND',
                conditions: []
            });
            this.updateValue();
        },

        removeGroup(groupIndex) {
            this.groups.splice(groupIndex, 1);
            this.updateValue();
        },

        addConditionToGroup(groupIndex) {
            const firstField = this.fields[0];
            const defaultOperator = this.getOperatorsForType(firstField?.value)[0]?.value || '=';
            let defaultValue = '';

            if (firstField?.type === 'date') {
                defaultValue = {
                    type: 'relative',
                    value: 'TODAY'
                };
            } else if (this.needsBetweenInput(defaultOperator)) {
                defaultValue = { min: '', max: '' };
            }

            this.groups[groupIndex].conditions.push({
                attribute: firstField?.value || '',
                operator: defaultOperator,
                value: defaultValue
            });
        },

        removeCondition(groupIndex, conditionIndex) {
            this.groups[groupIndex].conditions.splice(conditionIndex, 1);
            this.updateValue();
        },

        getField(fieldValue) {
            return this.fields.find(field => field.value === fieldValue);
        },

        getFieldOperators(fieldType) {
            return this.operators[fieldType] || this.operators.text;
        },

        getFieldOptions(fieldValue) {
            const field = this.fields.find(field => field.value === fieldValue);
            return field?.options || [];
        },

        isDateField(fieldValue) {
            const field = this.fields.find(field => field.value === fieldValue);
            return field?.type === 'date';
        },

        isMultiSelectVisible(condition) {
            const field = this.fields.find(field => field.value === condition.attribute);
            return field?.type === 'select' && ['IN', 'NOT IN'].includes(condition.operator);
        },

        isSingleSelectVisible(condition) {
            const field = this.fields.find(field => field.value === condition.attribute);
            return field?.type === 'select' && !['IN', 'NOT IN'].includes(condition.operator);
        },

        needsValueInput(operator) {
            const noValueOperators = ['IS_NULL', 'IS_NOT_NULL', 'THIS_WEEK', 'THIS_MONTH', 'THIS_YEAR'];
            return !noValueOperators.includes(operator);
        },

        needsBetweenInput(operator) {
            return ['BETWEEN', 'NOT_BETWEEN'].includes(operator);
        },

        needsDaysInput(operator) {
            return ['LAST_X_DAYS', 'NEXT_X_DAYS'].includes(operator);
        },

        getDateValue(condition) {
            if (!condition.value || typeof condition.value === 'string') {
                this.$set(condition, 'value', {
                    type: 'relative',
                    value: 'TODAY'
                });
            }

            const value = condition.value;

            if (value.type === 'relative') {
                if (value.value) {
                    return {
                        selectedOption: value.value,
                        offset: 1,
                        manualDate: ''
                    };
                } else if (value.base) {
                    const direction = value.offset >= 0 ? 'PLUS' : 'MINUS';
                    const selectedOption = `${value.base}_${direction}_${value.unit.toUpperCase()}`;
                    return {
                        selectedOption,
                        offset: Math.abs(value.offset),
                        manualDate: ''
                    };
                }
            } else if (value.type === 'manual') {
                return {
                    selectedOption: 'OTHER',
                    offset: 1,
                    manualDate: value.value
                };
            }

            // Fallback
            return {
                selectedOption: 'TODAY',
                offset: 1,
                manualDate: ''
            };
        },

        needsOffsetInput(selectedOption) {
            const offsetOptions = [
                'TODAY_PLUS_DAYS', 'TODAY_PLUS_WEEKS', 'TODAY_PLUS_MONTHS', 'TODAY_PLUS_YEARS',
                'TODAY_MINUS_DAYS', 'TODAY_MINUS_WEEKS', 'TODAY_MINUS_MONTHS', 'TODAY_MINUS_YEARS'
            ];
            return offsetOptions.includes(selectedOption);
        },

        getOffsetLabel(selectedOption) {
            const labelMap = {
                'TODAY_PLUS_DAYS': __('Days'),
                'TODAY_MINUS_DAYS': __('Days'),
                'TODAY_PLUS_WEEKS': __('Weeks'),
                'TODAY_MINUS_WEEKS': __('Weeks'),
                'TODAY_PLUS_MONTHS': __('Months'),
                'TODAY_MINUS_MONTHS': __('Months'),
                'TODAY_PLUS_YEARS': __('Years'),
                'TODAY_MINUS_YEARS': __('Years')
            };
            return labelMap[selectedOption] || '';
        },

        getDateValueOptions() {
            return [
                { value: 'TODAY', label: this.__('Today'), group: this.__('Fixed Relatives') },
                { value: 'TOMORROW', label: this.__('Tomorrow'), group: this.__('Fixed Relatives') },
                { value: 'YESTERDAY', label: this.__('Yesterday'), group: this.__('Fixed Relatives') },

                { value: 'TODAY_PLUS_DAYS', label: this.__('Today + X days'), group: this.__('Dynamic Relatives') },
                { value: 'TODAY_PLUS_WEEKS', label: this.__('Today + X weeks'), group: this.__('Dynamic Relatives') },
                { value: 'TODAY_PLUS_MONTHS', label: this.__('Today + X months'), group: this.__('Dynamic Relatives') },
                { value: 'TODAY_PLUS_YEARS', label: this.__('Today + X years'), group: this.__('Dynamic Relatives') },
                { value: 'TODAY_MINUS_DAYS', label: this.__('Today - X days'), group: this.__('Dynamic Relatives') },
                { value: 'TODAY_MINUS_WEEKS', label: this.__('Today - X weeks'), group: this.__('Dynamic Relatives') },
                { value: 'TODAY_MINUS_MONTHS', label: this.__('Today - X months'), group: this.__('Dynamic Relatives') },
                { value: 'TODAY_MINUS_YEARS', label: this.__('Today - X years'), group: this.__('Dynamic Relatives') },

                { value: 'OTHER', label: this.__('Other...'), group: this.__('Manual') }
            ];
        },

        onDateOptionChange(groupIndex, conditionIndex, selectedOption) {
            const condition = this.groups[groupIndex].conditions[conditionIndex];

            if (['TODAY', 'TOMORROW', 'YESTERDAY'].includes(selectedOption)) {
                condition.value = {
                    type: 'relative',
                    value: selectedOption
                };
            } else if (this.needsOffsetInput(selectedOption)) {
                const parts = selectedOption.split('_');
                const base = parts[0];
                const direction = parts[1];
                const unit = parts[2].toLowerCase();

                condition.value = {
                    type: 'relative',
                    base: base,
                    offset: direction === 'MINUS' ? -1 : 1,
                    unit: unit
                };
            } else if (selectedOption === 'OTHER') {
                condition.value = {
                    type: 'manual',
                    value: ''
                };
            }

            this.updateValue();
        },

        updateDateOffset(groupIndex, conditionIndex, offset) {
            const condition = this.groups[groupIndex].conditions[conditionIndex];
            const currentValue = condition.value;

            if (currentValue.type === 'relative' && currentValue.base) {
                const direction = currentValue.offset >= 0 ? 1 : -1;
                condition.value = {
                    ...currentValue,
                    offset: direction * Math.abs(parseInt(offset) || 1)
                };
                this.updateValue();
            }
        },

        updateManualDate(groupIndex, conditionIndex, date) {
            const condition = this.groups[groupIndex].conditions[conditionIndex];

            condition.value = {
                type: 'manual',
                value: date
            };
            this.updateValue();
        },

        getOperatorsForType(fieldName) {
            const field = this.fields.find(field => field.value === fieldName);
            return field ? this.operators[field.type] || [] : [];
        },

        updateCondition(groupIndex, conditionIndex) {
            const condition = this.groups[groupIndex].conditions[conditionIndex];
            const operator = condition.operator;

            if (this.needsBetweenInput(operator)) {
                condition.value = { min: '', max: '' };
            } else if (!this.needsValueInput(operator)) {
                condition.value = null;
            } else if (this.isDateField(condition.attribute)) {
                condition.value = {
                    type: 'relative',
                    value: 'TODAY'
                };
            } else if (this.needsDaysInput(operator)) {
                condition.value = condition.value || 1;
            }

            this.updateValue();
        },

        getBetweenValue(condition) {
            if (!condition.value || typeof condition.value === 'string') {
                this.$set(condition, 'value', { min: '', max: '' });
            } else if (Array.isArray(condition.value)) {
                this.$set(condition, 'value', {
                    min: condition.value[0] || '',
                    max: condition.value[1] || ''
                });
            }
            return condition.value;
        },

        updateBetweenValue(groupIndex, conditionIndex) {
            const condition = this.groups[groupIndex].conditions[conditionIndex];
            const value = condition.value;

            condition.value = [value.min, value.max];
            this.updateValue();
        },

        updateValue() {
            this.$emit('input', {
                groups: this.groups,
                globalConjunction: this.globalConjunction,
                limit: parseInt(this.limit) || this.defaultLimit
            });
        },

        moveGroupUp(groupIndex) {
            if (groupIndex > 0) {
                const group = this.groups[groupIndex];
                this.$set(this.groups, groupIndex, this.groups[groupIndex - 1]);
                this.$set(this.groups, groupIndex - 1, group);
                this.updateValue();
            }
        },

        moveGroupDown(groupIndex) {
            if (groupIndex < this.groups.length - 1) {
                const group = this.groups[groupIndex];
                this.$set(this.groups, groupIndex, this.groups[groupIndex + 1]);
                this.$set(this.groups, groupIndex + 1, group);
                this.updateValue();
            }
        }
    },

    mounted() {
        this.groups = this.initializeGroups();
        if (this.value && this.value.limit) {
            this.limit = this.value.limit;
        }
    }
}
</script>

<style scoped>
.form-select {
    @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500;
}

optgroup {
    font-weight: bold;
    color: #333;
    background-color: #f8f9fa;
}

optgroup option {
    font-weight: normal;
    color: #000;
    padding-left: 16px;
    background-color: white;
}
</style>