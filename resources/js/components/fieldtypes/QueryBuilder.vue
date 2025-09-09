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
            <div class="flex items-center space-x-2">
                <div class="flex items-center space-x-2" v-if="groupedPresets.length > 0">
                    <label class="text-sm">{{ __('Presets') }}</label>
                    <v-select
                        :options="groupedPresets"
                        :reduce="preset => preset"
                        label="name"
                        :placeholder="__('Select Preset')"
                        class="w-48"
                        @input="handlePresetSelection"
                    >
                        <template #option="option">
                            <div v-if="option.isHeader" class="font-semibold text-gray-600 px-2 py-1 bg-gray-50 border-b">
                                {{ option.name }}
                            </div>
                            <div v-else class="px-4 py-2 hover:bg-blue-50">
                                <div class="font-medium">{{ option.name }}</div>
                                <div v-if="option.description" class="text-sm text-gray-500 mt-1">
                                    {{ option.description }}
                                </div>
                            </div>
                        </template>
                    </v-select>
                </div>
                <button class="btn-primary self-start" @click="addGroup">{{ __('Add Group') }}</button>
            </div>
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
                    :fields="flattenedFields"
                    :operators="operators"
                    :display-index="groupIndex + 1"
                    :can-move-up="groupIndex > 0"
                    :can-move-down="groupIndex < groups.length - 1"
                    :can-remove="groups.length > 1"
                    @update-group="updateGroup"
                    @remove-group="removeGroup"
                    @duplicate-group="duplicateGroup"
                    @move-group-up="moveGroupUp"
                    @move-group-down="moveGroupDown"
                    @add-condition="addConditionToGroup"
                    @add-nested-group="addNestedGroupToGroup"
                    @add-condition-to-nested="addConditionToNestedGroup"
                    @add-nested-group-to-nested="addNestedGroupToNestedGroup"
                    @update-condition="updateCondition"
                    @update-nested-condition="updateNestedCondition"
                    @remove-condition="removeCondition"
                    @remove-nested-condition="removeNestedCondition"
                />

                <div v-if="groupIndex < groups.length" :key="`separator-${groupIndex}`" class="flex flex-col items-center space-y-2">
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

        <div v-if="showConflictModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">{{ __('Apply Preset') }}</h3>
                <p class="text-gray-600 mb-6">
                    {{ __('You have an existing query. How would you like to apply this preset?') }}
                </p>
                <div class="flex space-x-3">
                    <button
                        class="btn flex-1"
                        @click="applyPreset('merge')"
                    >
                        {{ __('Merge') }}
                    </button>
                    <button
                        class="btn-primary flex-1"
                        @click="applyPreset('override')"
                    >
                        {{ __('Override') }}
                    </button>
                    <button
                        class="btn flex-1"
                        @click="cancelPresetApplication"
                    >
                        {{ __('Cancel') }}
                    </button>
                </div>
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
                if (value.length === 0) {
                    return true
                }

                if (value[0].label && value[0].options) {
                    return value.every(group =>
                        'label' in group &&
                        'options' in group &&
                        group.options.every(field =>
                            'label' in field &&
                            'value' in field &&
                            'type' in field
                        )
                    );
                }

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
            presets: [],
            showConflictModal: false,
            pendingPreset: null,
        }
    },

    computed: {
        flattenedFields() {
            if (!this.fields[0]?.options) {
                return this.fields;
            }

            return this.fields.flatMap(group => group.options);
        },

        groupedPresets() {
            const grouped = [];

            Object.keys(this.presets).forEach(categoryKey => {
                const category = this.presets[categoryKey];

                grouped.push({
                    name: category.label,
                    category: category.label,
                    disabled: true,
                    isHeader: true
                });

                category.presets.forEach(preset => {
                    grouped.push({
                        ...preset,
                        categoryKey,
                        isHeader: false
                    });
                });
            });

            return grouped;
        }
    },

    methods: {
        async fetchPresets() {
            try {
                const response = await this.$axios.get('/cp/rapidez/query-presets');
                if (response.data.success) {
                    this.presets = response.data.categories;
                } else {
                    this.$toast.error(__('Failed to load query presets'));
                }
            } catch (error) {
                console.error('Error fetching presets:', error);
                this.$toast.error(__('Error loading presets'));
            }
        },

        handlePresetSelection(preset) {
            if (!preset || preset.disabled || preset.isHeader) return;

            if (this.groups.length > 0) {
                this.pendingPreset = preset;
                this.showConflictModal = true;
            } else {
                this.applyPresetDirectly(preset);
            }
        },

        applyPreset(action) {
            this.showConflictModal = false;

            if (action === 'override') {
                this.groups = [];
                this.applyPresetDirectly(this.pendingPreset);
            } else if (action === 'merge') {
                this.applyPresetDirectly(this.pendingPreset, true);
            }

            this.pendingPreset = null;
        },

        cancelPresetApplication() {
            this.showConflictModal = false;
            this.pendingPreset = null;
        },

        applyPresetDirectly(preset, merge = false) {
            const validatedQuery = this.validateAndCorrectPreset(preset.query);

            if (merge) {
                this.groups.push(...validatedQuery.groups);
            } else {
                this.groups = validatedQuery.groups;
                this.globalConjunction = validatedQuery.globalConjunction;
            }

            this.updateValue();
        },

        validateAndCorrectPreset(query) {
            const validatedQuery = {
                groups: [],
                globalConjunction: query.globalConjunction || 'AND'
            };

            const validationIssues = [];

            query.groups.forEach((group, groupIndex) => {
                const validatedGroup = this.validateGroup(group, validationIssues);
                if (validatedGroup.conditions.length > 0) {
                    validatedQuery.groups.push(validatedGroup);
                }
            });

            if (validationIssues.length > 0) {
                validationIssues.forEach(issue => {
                    this.$toast.info(issue);
                });
            }

            return validatedQuery;
        },

        validateGroup(group, validationIssues) {
            const validatedGroup = {
                conjunction: group.conjunction || 'AND',
                conditions: []
            };

            group.conditions.forEach((item) => {
                if (item.type === 'group') {
                    const nestedGroup = this.validateGroup(item, validationIssues);
                    if (nestedGroup.conditions.length > 0) {
                        validatedGroup.conditions.push({
                            ...nestedGroup,
                            type: 'group'
                        });
                    }
                } else {
                    const validatedCondition = this.validateCondition(item, validationIssues);
                    if (validatedCondition) {
                        validatedGroup.conditions.push(validatedCondition);
                    }
                }
            });

            return validatedGroup;
        },

        validateCondition(condition, validationIssues) {
            const field = this.flattenedFields.find(f => f.value === condition.attribute);

            if (!field) {
                validationIssues.push(`Field "${condition.attribute}" not found - skipping condition`);
                return null;
            }

            const validatedCondition = { ...condition };

            const fieldOperators = this.getOperatorsForField(field);
            const operatorExists = fieldOperators.some(op => op.value === condition.operator);

            if (!operatorExists) {
                const fallbackOperator = fieldOperators[0]?.value || '=';
                validationIssues.push(`Operator "${condition.operator}" not valid for field "${field.label}" - using "${fallbackOperator}"`);
                validatedCondition.operator = fallbackOperator;
            }

            return validatedCondition;
        },

        getOperatorsForField(field) {
            if (field && field.operators) {
                return field.operators.map(op => ({
                    value: op,
                    label: this.getOperatorLabel(op)
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

        createDefaultGroup() {
            return {
                name: '',
                conjunction: 'AND',
                conditions: []
            };
        },

        addGroup() {
            this.groups.push(this.createDefaultGroup());
            this.updateValue();
        },

        insertGroupAt(index) {
            this.groups.splice(index, 0, this.createDefaultGroup());
            this.updateValue();
        },

        duplicateGroup(groupIndex) {
            const group = this.groups[groupIndex];
            this.groups.splice(groupIndex + 1, 0, { ...group });
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

        addNestedGroupToGroup(groupIndex) {
            const nestedGroup = {
                type: 'group',
                ...this.createDefaultGroup()
            };
            this.groups[groupIndex].conditions.push(nestedGroup);
            this.updateValue();
        },

        addConditionToNestedGroup(groupIndex, nestedGroupIndex) {
            const condition = this.createDefaultCondition();
            this.groups[groupIndex].conditions[nestedGroupIndex].conditions.push(condition);
            this.updateValue();
        },

        addNestedGroupToNestedGroup(groupIndex, nestedGroupIndex) {
            const nestedGroup = {
                type: 'group',
                ...this.createDefaultGroup()
            };
            this.groups[groupIndex].conditions[nestedGroupIndex].conditions.push(nestedGroup);
            this.updateValue();
        },

        updateNestedCondition(groupIndex, nestedGroupIndex, conditionIndex, condition) {
            this.$set(this.groups[groupIndex].conditions[nestedGroupIndex].conditions, conditionIndex, condition);
            this.updateValue();
        },

        removeNestedCondition(groupIndex, nestedGroupIndex, conditionIndex) {
            this.groups[groupIndex].conditions[nestedGroupIndex].conditions.splice(conditionIndex, 1);
            this.updateValue();
        },

        createDefaultCondition() {
            const firstField = this.flattenedFields[0];
            const defaultOperator = this.getOperatorsForField(firstField)[0]?.value || '=';
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

        updateValue() {
            const payload = {
                groups: this.groups,
                globalConjunction: this.globalConjunction,
                limit: this.limit,
                builderTemplate: this.builderTemplate,
                sortField: this.sortField,
                sortDirection: this.sortDirection,
            };
            this.$emit('input', payload);
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
        this.fetchPresets();
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
