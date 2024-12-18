<template>
    <div class="query-builder">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center justify-between w-full space-x-4">
                <div class="flex items-center space-x-2" v-if="showLimit">
                    <label for="limit" class="text-sm">Limit results:</label>
                    <input 
                        type="number" 
                        id="limit" 
                        v-model="limit" 
                        class="input-text w-24" 
                        min="1"
                        @input="updateValue"
                    >
                </div>
                <button class="btn-primary" @click="addGroup">Add Group</button>
            </div>
        </div>

        <div class="space-y-6">
            <div v-for="(group, groupIndex) in groups" :key="groupIndex" class="border border-gray-300 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
                    <div class="flex items-center space-x-4">
                        <h3 class="text-base font-bold">Group {{ groupIndex + 1 }}</h3>
                        <v-select
                            v-model="group.conjunction"
                            :options="logicalOperators"
                            class="w-32"
                            @input="updateValue"
                        />
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="btn" @click="addConditionToGroup(groupIndex)">Add Condition</button>
                        <div class="flex items-center space-x-1">
                            <button 
                                v-if="groupIndex > 0"
                                class="btn p-2" 
                                @click="moveGroupUp(groupIndex)" 
                                title="Move group up"
                            >
                                ↑
                            </button>
                            <button 
                                v-if="groupIndex < groups.length - 1"
                                class="btn p-2" 
                                @click="moveGroupDown(groupIndex)" 
                                title="Move group down"
                            >
                                ↓
                            </button>
                        </div>
                        <button class="btn-danger" @click="removeGroup(groupIndex)">Remove Group</button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div v-for="(condition, conditionIndex) in group.conditions" :key="conditionIndex" 
                         class="flex items-center space-x-4 p-3 bg-gray-50 rounded-md">
                        <v-select
                            v-model="condition.attribute"
                            :options="fields"
                            :reduce="field => field.value"
                            label="label"
                            class="w-1/3"
                            placeholder="Select Field"
                            @input="updateCondition(groupIndex, conditionIndex)"
                        />

                        <v-select
                            v-model="condition.operator"
                            :options="getOperatorsForType(condition.attribute)"
                            :reduce="op => op.value"
                            label="label"
                            class="w-1/4"
                            placeholder="Select Operator"
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
                                    placeholder="Select Values"
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
                                    placeholder="Select Value"
                                    @input="updateCondition(groupIndex, conditionIndex)"
                                />
                            </template>
                            <template v-else-if="needsBetweenInput(condition.operator)">
                                <div class="flex items-center space-x-2 flex-1">
                                    <input 
                                        type="text"
                                        v-model="getBetweenValue(condition).min"
                                        class="input-text flex-1"
                                        :placeholder="'Min value'"
                                        @input="updateBetweenValue(groupIndex, conditionIndex)"
                                    >
                                    <span>and</span>
                                    <input 
                                        type="text"
                                        v-model="getBetweenValue(condition).max"
                                        class="input-text flex-1"
                                        :placeholder="'Max value'"
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
                                        placeholder="Number of days"
                                        min="1"
                                        @input="updateCondition(groupIndex, conditionIndex)"
                                    >
                                    <span>days</span>
                                </div>
                            </template>
                            <template v-else>
                                <input 
                                    type="text"
                                    v-model="condition.value"
                                    class="input-text flex-1"
                                    placeholder="Enter value"
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
                <p class="text-gray-500 mb-4">No groups added yet. Click "Add Group" to start building your query.</p>
            </div>
        </div>

        <div v-if="groups.length > 1" class="mt-6 pt-4 border-t border-gray-200">
            <div class="flex items-center space-x-4">
                <label class="font-bold">Combine Groups with:</label>
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
                    { value: '=', label: 'Is exactly' },
                    { value: '!=', label: 'Is not' },
                    { value: 'LIKE', label: 'Contains' },
                    { value: 'NOT LIKE', label: 'Does not contain' },
                    { value: 'STARTS_WITH', label: 'Starts with' },
                    { value: 'ENDS_WITH', label: 'Ends with' },
                    { value: 'IS_NULL', label: 'Is empty' },
                    { value: 'IS_NOT_NULL', label: 'Is not empty' }
                ],
                select: [
                    { value: '=', label: 'Is' },
                    { value: '!=', label: 'Is not' },
                    { value: 'IN', label: 'Is any of' },
                    { value: 'NOT IN', label: 'Is none of' },
                    { value: 'IS_NULL', label: 'Nothing selected' },
                    { value: 'IS_NOT_NULL', label: 'Has selection' }
                ],
                number: [
                    { value: '=', label: 'Equals' },
                    { value: '!=', label: 'Does not equal' },
                    { value: '>', label: 'Greater than' },
                    { value: '<', label: 'Less than' },
                    { value: '>=', label: 'Greater than or equal to' },
                    { value: '<=', label: 'Less than or equal to' },
                    { value: 'BETWEEN', label: 'Is between' },
                    { value: 'NOT_BETWEEN', label: 'Is not between' },
                    { value: 'IS_NULL', label: 'Is empty' },
                    { value: 'IS_NOT_NULL', label: 'Is not empty' }
                ],
                date: [
                    { value: '=', label: 'Is on' },
                    { value: '!=', label: 'Is not on' },
                    { value: '>', label: 'Is after' },
                    { value: '<', label: 'Is before' },
                    { value: '>=', label: 'Is on or after' },
                    { value: '<=', label: 'Is on or before' },
                    { value: 'BETWEEN', label: 'Is between' },
                    { value: 'NOT_BETWEEN', label: 'Is not between' },
                    { value: 'LAST_X_DAYS', label: 'In the last days' },
                    { value: 'NEXT_X_DAYS', label: 'In the next days' },
                    { value: 'THIS_WEEK', label: 'This week' },
                    { value: 'THIS_MONTH', label: 'This month' },
                    { value: 'THIS_YEAR', label: 'This year' },
                    { value: 'IS_NULL', label: 'No date set' },
                    { value: 'IS_NOT_NULL', label: 'Has date' }
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
            const defaultOperator = this.getOperatorsForType(this.fields[0]?.value)[0]?.value || '=';
            const defaultValue = this.needsBetweenInput(defaultOperator) ? { min: '', max: '' } : '';
            
            this.groups[groupIndex].conditions.push({
                attribute: this.fields[0]?.value || '',
                operator: defaultOperator,
                value: defaultValue
            });
        },

        removeCondition(groupIndex, conditionIndex) {
            this.groups[groupIndex].conditions.splice(conditionIndex, 1);
            this.updateValue();
        },

        getField(fieldValue) {
            return this.fields.find(f => f.value === fieldValue);
        },

        getFieldOperators(fieldType) {
            return this.operators[fieldType] || this.operators.text;
        },

        getFieldOptions(fieldValue) {
            const field = this.fields.find(f => f.value === fieldValue);
            return field?.options || [];
        },

        isMultiSelectVisible(condition) {
            const field = this.fields.find(f => f.value === condition.attribute);
            return field?.type === 'select' && ['IN', 'NOT IN'].includes(condition.operator);
        },

        isSingleSelectVisible(condition) {
            const field = this.fields.find(f => f.value === condition.attribute);
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

        getOperatorsForType(fieldName) {
            const field = this.fields.find(f => f.value === fieldName);
            return field ? this.operators[field.type] || [] : [];
        },

        updateCondition(groupIndex, conditionIndex) {
            const condition = this.groups[groupIndex].conditions[conditionIndex];
            const operator = condition.operator;
            
            if (this.needsBetweenInput(operator)) {
                condition.value = { min: '', max: '' };
            } else if (!this.needsValueInput(operator)) {
                condition.value = null;
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
                const temp = this.groups[groupIndex];
                this.$set(this.groups, groupIndex, this.groups[groupIndex - 1]);
                this.$set(this.groups, groupIndex - 1, temp);
                this.updateValue();
            }
        },

        moveGroupDown(groupIndex) {
            if (groupIndex < this.groups.length - 1) {
                const temp = this.groups[groupIndex];
                this.$set(this.groups, groupIndex, this.groups[groupIndex + 1]);
                this.$set(this.groups, groupIndex + 1, temp);
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
.query-builder {
    @apply max-w-4xl mx-auto;
}
</style>
