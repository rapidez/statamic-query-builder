<template>
    <div class="query-builder max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <div class="flex flex-col justify-between w-full space-x-4 gap-4">
                <div class="flex gap-2">
                    <div class="flex items-center space-x-2" v-if="showLimit">
                        <label for="limit" class="text-sm">{{ __('Limit results') }}</label>
                        <input
                            type="number"
                            name="limit"
                            id="limit"
                            v-model="limit"
                            class="input-text w-24"
                            min="1"
                            @input="updateSettingValues"
                        >
                    </div>
                    <div class="flex items-center space-x-2" v-if="builderTemplates">
                        <label for="template" class="text-sm">{{ __('Template') }}</label>
                        <v-select
                            name="builderTemplate"
                            v-model="builderTemplate"
                            :options="builderTemplates"
                            :reduce="field => field.value"
                            class="w-36"
                            @input="updateSettingValues"
                        />
                    </div>
                </div>
                <div class="flex gap-2">
                    <div class="flex items-center space-x-2" v-if="sortFields">
                        <label for="template" class="text-sm">{{ __('Sort') }}</label>
                        <v-select
                            name="sortField"
                            v-model="sortField"
                            :options="sortFields"
                            :reduce="field => field.value"
                            class="w-36"
                            @input="updateSettingValues"
                        />
                    </div>
                    <div class="flex items-center space-x-2" v-if="sortFields">
                        <label for="template" class="text-sm">{{ __('Sort Direction') }}</label>
                        <v-select
                            name="sortDirection"
                            v-model="sortDirection"
                            :options="sortDirections"
                            class="w-36"
                            @input="updateSettingValues"
                        />
                    </div>
                </div>
            </div>
            <button class="btn-primary self-start" @click="addGroup">{{ __('Add Group') }}</button>
        </div>

        <div class="space-y-6">
            <div v-if="groups.length > 0" class="flex justify-center">
                <button
                    class="insert-group-btn"
                    @click="insertGroupAt(0)"
                    :title="__('Insert group here')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </button>
            </div>

            <template v-for="(group, groupIndex) in groups">
                <query-group
                    :key="`group-${groupIndex}`"
                    :group="group"
                    :group-index="groupIndex"
                    :fields="fields"
                    :operators="operators"
                    :can-move-up="groupIndex > 0"
                    :can-move-down="groupIndex < groups.length - 1"
                    :can-remove="groups.length > 1"
                    @update-group="updateGroup"
                    @remove-group="removeGroup"
                    @move-group-up="moveGroupUp"
                    @move-group-down="moveGroupDown"
                    @add-condition="addConditionToGroup"
                    @update-condition="updateCondition"
                    @remove-condition="removeCondition"
                />

                <div v-if="groupIndex < groups.length - 1" :key="`separator-${groupIndex}`" class="flex flex-col items-center space-y-2">
                    <button
                        class="insert-group-btn"
                        @click="insertGroupAt(groupIndex + 1)"
                        :title="__('Insert group here')"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <div v-if="!groups.length" class="text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-500 mb-4">{{ __('No groups added yet. Click "Add Group" to start building your query.') }}</p>
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
import QueryGroup from '../QueryBuilder/QueryGroup.vue';
import FieldHelpers from '../../mixins/FieldHelpers.js';

export default {
    components: {
        QueryGroup
    },

    mixins: [Fieldtype, FieldHelpers],

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
        sortFields: {
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
        defaultSortField: {
            type: String,
            default: ''
        },
        defaultSortDirection: {
            type: String,
            default: 'desc'
        },
        defaultBuilderTemplate: {
            type: String,
            default: ''
        },
        builderTemplates: {
            type: Array,
            default: []
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
            builderTemplate: '',
            limit: 100,
            globalConjunction: 'AND',
            logicalOperators: ['AND', 'OR'],
            sortField: '',
            sortDirection: '',
            sortDirections: ['ASC', 'DESC'],
        }
    },

    methods: {
        initializeLimit() {
            if (this.value?.limit) {
                return this.value.limit;
            }
            return this.defaultLimit;
        },

        initializeSortField() {
            if (this.value?.sortField) {
                return this.value.sortField;
            }
            return this.defaultSortField;
        },

        initializeSortDirection() {
            if (this.value?.sortDirection) {
                return this.value.sortDirection;
            }
            return this.defaultSortDirection;
        },

        initializeBuilderTemplate() {
            if (this.value?.builderTemplate) {
                return this.value.builderTemplate;
            }
            return this.defaultBuilderTemplate;
        },

        addGroup() {
            this.groups.push({
                conjunction: 'AND',
                conditions: []
            });
            this.updateValue();
        },

        insertGroupAt(index) {
            const newGroup = {
                conjunction: 'AND',
                conditions: []
            };
            this.groups.splice(index, 0, newGroup);
            this.updateValue();
        },

        removeGroup(groupIndex) {
            this.groups.splice(groupIndex, 1);
            this.updateValue();
        },

        updateGroup(groupIndex, group) {
            this.$set(this.groups, groupIndex, group);
            this.updateValue();
        },

        addConditionToGroup(groupIndex) {
            const condition = this.createDefaultCondition();
            this.groups[groupIndex].conditions.push(condition);
            this.updateValue();
        },

        updateCondition(groupIndex, conditionIndex, condition) {
            this.$set(this.groups[groupIndex].conditions, conditionIndex, condition);
            this.updateValue();
        },

        removeCondition(groupIndex, conditionIndex) {
            this.groups[groupIndex].conditions.splice(conditionIndex, 1);
            this.updateValue();
        },

        createDefaultCondition() {
            const firstField = this.fields[0];
            const defaultOperator = this.getOperatorsForType(firstField?.value)[0]?.value || '=';
            let defaultValue = '';

            if (firstField?.type === 'date') {
                defaultValue = {
                    type: 'relative',
                    value: 'TODAY'
                };
            }

            return {
                attribute: firstField?.value || '',
                operator: defaultOperator,
                value: defaultValue
            };
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
        },

        updateValue() {
            this.$emit('input', {
                groups: this.groups,
                globalConjunction: this.globalConjunction,
            });
        },

        updateSettingValues() {
            this.value.limit = parseInt(this.limit) || this.defaultLimit;
            this.value.builderTemplate = this.builderTemplate || this.defaultBuilderTemplate;
            this.value.sortField = this.sortField || this.defaultSortField;
            this.value.sortDirection = this.sortDirection || this.defaultSortDirection;
        },
    },

    mounted() {
        this.groups = this.initializeGroups();
        this.limit = this.initializeLimit();
        this.sortField = this.initializeSortField();
        this.sortDirection = this.initializeSortDirection();
        this.builderTemplate = this.initializeBuilderTemplate();
    }
}
</script>

<style scoped>
.insert-group-btn {
    @apply flex items-center justify-center w-8 h-8 bg-blue-50 border-2 border-dashed border-blue-300 rounded-full text-blue-500 hover:bg-blue-100 hover:border-blue-400 transition-colors duration-200;
}

.insert-group-btn:hover {
    @apply shadow-sm;
}
</style>
