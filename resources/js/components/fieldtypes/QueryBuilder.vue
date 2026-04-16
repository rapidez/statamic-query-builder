<template>
    <div class="query-builder max-w-4xl mx-auto">
        
            <div class="flex flex-col justify-between w-full space-x-4 gap-4">
                <div class="flex gap-2">
                    <Field v-if="showUseDefaultQueryToggle" class="self-end">
                        <Checkbox
                            id="useDefaultQuery"
                            :label="__('Use default Query')"
                            v-model="useDefaultQuery"
                            @change="(e) => { useDefaultQuery = e.target.checked; updateSettingValues(); }"
                        />
                    </Field>
                    <Field v-if="builderTemplates">
                        <Label for="template" class="text-sm">{{ __('Template') }}</Label>
                        <Select
                            name="builderTemplate"
                            :model-value="builderTemplate"
                            :options="builderTemplates"
                            :reduce="field => field.value"
                            class="w-36"
                            @update:model-value="(val) => { builderTemplate = val; updateSettingValues(); }"
                        />
                    </Field>
                    <Field v-if="isLimitVisible">
                        <Label for="limit" class="text-sm">{{ __('Limit results') }}</Label>
                        <Input
                            type="number"
                            name="limit"
                            id="limit"
                            :value="limit"
                            class="input-text w-24"
                            min="1"
                            @input="(e) => { limit = parseInt(e.target.value) || props.defaultLimit; updateSettingValues(); }"
                        />
                    </Field>
                </div>
                <div class="flex gap-2">
                    <Field v-if="sortFields">
                        <Label for="template" class="text-sm">{{ __('Sort') }}</Label>
                        <Select
                            name="sortField"
                            :model-value="sortField"
                            :options="sortFields"
                            :reduce="field => field.value"
                            class="w-36"
                            @update:model-value="(val) => { sortField = val; updateSettingValues(); }"
                        />
                    </Field>
                    <Field v-if="sortFields">
                        <Label for="template" class="text-sm">{{ __('Sort Direction') }}</Label>
                        <Select
                            name="sortDirection"
                            :model-value="sortDirection"
                            :options="sortDirections"
                            class="w-36"
                            @update:model-value="(val) => { sortDirection = val; updateSettingValues(); }"
                        />
                    </Field>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <Field v-if="groupedPresets.length > 0">
                    <Label class="text-sm">{{ __('Presets') }}</Label>
                    <Select
                        :options="groupedPresets"
                        :reduce="preset => preset"
                        label="name"
                        :placeholder="__('Select Preset')"
                        class="w-48"
                        @update:model-value="handlePresetSelection"
                    >
                        <template #option="{ name, isHeader, description  }">
                            <div v-if="isHeader" class="font-semibold text-gray-600 px-2 py-1 bg-gray-50 border-b">
                                {{ name }}
                            </div>
                            <div v-else class="px-4 py-2 hover:bg-blue-50">
                                <div class="font-medium">{{ name }}</div>
                                <div v-if="description" class="text-sm text-gray-500 mt-1">
                                    {{ description }}
                                </div>
                            </div>
                        </template>
                    </Select>
                </Field>
                <Button class="btn-primary" @click="addGroup">{{ __('Add Group') }}</Button>
            </div>


        <div class="space-y-6">
            <div v-if="groups.length > 0" class="flex justify-center">
                <Button
                    class="insert-group-btn"
                    @click="insertGroupAt(0)"
                    :title="__('Insert group here')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </Button>
            </div>

            <template v-for="(group, groupIndex) in groups" :key="`group-${groupIndex}`">
                <query-group
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
                    <Button
                        class="insert-group-btn"
                        @click="insertGroupAt(groupIndex + 1)"
                        :title="__('Insert group here')"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </Button>
                </div>
            </template>
        </div>

        <div v-if="!groups.length" class="text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-500 mb-4">{{ __('No groups added yet. Click "Add Group" to start building your query.') }}</p>
        </div>

        <div v-if="groups.length > 1" class="mt-6 pt-4 border-t border-gray-200">
            <Field>
                <Label class="font-bold">{{ __('Combine Groups with:') }}</Label>
                <Select
                    :model-value="globalConjunction"
                    :options="logicalOperators"
                    class="w-32"
                    @update:model-value="(val) => { globalConjunction = val; updateValue(); }"
                />
            </Field>
        </div>

        <div v-if="showConflictModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">{{ __('Apply Preset') }}</h3>
                <p class="text-gray-600 mb-6">
                    {{ __('You have an existing query. How would you like to apply this preset?') }}
                </p>
                <div class="flex space-x-3">
                    <Button
                        class="btn flex-1"
                        @click="applyPreset('merge')"
                    >
                        {{ __('Merge') }}
                    </Button>
                    <Button
                        class="btn-primary flex-1"
                        @click="applyPreset('override')"
                    >
                        {{ __('Override') }}
                    </Button>
                    <Button
                        class="btn flex-1"
                        @click="cancelPresetApplication"
                    >
                        {{ __('Cancel') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import axios from 'axios';
import QueryGroup from '../QueryBuilder/QueryGroup.vue';
import { Select, Input, Field, Label, Checkbox, Button } from '@statamic/cms/ui';;

const props = defineProps({
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
    },
    showUseDefaultQueryToggle: {
        type: Boolean,
        default: true
    },
    value: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['update:model-value']);

const instance = getCurrentInstance();
const $toast = instance?.appContext.config.globalProperties.$toast;

const groups = ref([]);
const builderTemplate = ref('');
const limit = ref(100);
const useDefaultQuery = ref(true);
const globalConjunction = ref('AND');
const logicalOperators = ['AND', 'OR'];
const sortField = ref('');
const sortDirection = ref('');
const sortDirections = ['ASC', 'DESC'];
const presets = ref({});
const showConflictModal = ref(false);
const pendingPreset = ref(null);

const flattenedFields = computed(() => {
    if (!props.fields[0]?.options) {
        return props.fields;
    }
    return props.fields.flatMap(group => group.options);
});

const groupedPresets = computed(() => {
    const grouped = [];
    Object.keys(presets.value).forEach(categoryKey => {
        const category = presets.value[categoryKey];
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
});

const currentTemplateConfig = computed(() => {
    if (!props.builderTemplates || !Array.isArray(props.builderTemplates)) {
        return null;
    }
    return props.builderTemplates.find(template => (template.value || template) === builderTemplate.value) || null;
});

const isLimitVisible = computed(() => {
    if (!props.showLimit) {
        return false;
    }
    const config = currentTemplateConfig.value;
    if (config && typeof config === 'object' && config.hideLimit) {
        return false;
    }
    return true;
});

const fetchPresets = async () => {
    try {
        const response = await axios.get('/cp/rapidez/query-presets');
        if (response.data.success) {
            presets.value = response.data.categories;
        } else {
            $toast?.error(__('Failed to load query presets'));
        }
    } catch (error) {
        console.error('Error fetching presets:', error);
        $toast?.error(__('Error loading presets'));
    }
};

const handlePresetSelection = (preset) => {
    if (!preset || preset.disabled || preset.isHeader) return;

    if (groups.value.length > 0) {
        pendingPreset.value = preset;
        showConflictModal.value = true;
    } else {
        applyPresetDirectly(preset);
    }
};

const applyPreset = (action) => {
    showConflictModal.value = false;

    if (action === 'override') {
        groups.value = [];
        applyPresetDirectly(pendingPreset.value);
    } else if (action === 'merge') {
        applyPresetDirectly(pendingPreset.value, true);
    }

    pendingPreset.value = null;
};

const cancelPresetApplication = () => {
    showConflictModal.value = false;
    pendingPreset.value = null;
};

const applyPresetDirectly = (preset, merge = false) => {
    const validatedQuery = validateAndCorrectPreset(preset.query);

    if (merge) {
        groups.value.push(...validatedQuery.groups);
    } else {
        groups.value = validatedQuery.groups;
        globalConjunction.value = validatedQuery.globalConjunction;
    }

    updateValue();
};

const validateAndCorrectPreset = (query) => {
    const validatedQuery = {
        groups: [],
        globalConjunction: query.globalConjunction || 'AND'
    };

    const validationIssues = [];

    query.groups.forEach((group) => {
        const validatedGroup = validateGroup(group, validationIssues);
        if (validatedGroup.conditions.length > 0) {
            validatedQuery.groups.push(validatedGroup);
        }
    });

    if (validationIssues.length > 0) {
        validationIssues.forEach(issue => {
            $toast?.info(issue);
        });
    }

    return validatedQuery;
};

const validateGroup = (group, validationIssues) => {
    const validatedGroup = {
        conjunction: group.conjunction || 'AND',
        conditions: []
    };

    group.conditions.forEach((item) => {
        if (item.type === 'group') {
            const nestedGroup = validateGroup(item, validationIssues);
            if (nestedGroup.conditions.length > 0) {
                validatedGroup.conditions.push({
                    ...nestedGroup,
                    type: 'group'
                });
            }
        } else {
            const validatedCondition = validateCondition(item, validationIssues);
            if (validatedCondition) {
                validatedGroup.conditions.push(validatedCondition);
            }
        }
    });

    return validatedGroup;
};

const validateCondition = (condition, validationIssues) => {
    const field = flattenedFields.value.find(f => f.value === condition.attribute);

    if (!field) {
        validationIssues.push(`Field "${condition.attribute}" not found - skipping condition`);
        return null;
    }

    const validatedCondition = { ...condition };

    const fieldOperators = getOperatorsForField(field);
    const operatorExists = fieldOperators.some(op => op.value === condition.operator);

    if (!operatorExists) {
        const fallbackOperator = fieldOperators[0]?.value || '=';
        validationIssues.push(`Operator "${condition.operator}" not valid for field "${field.label}" - using "${fallbackOperator}"`);
        validatedCondition.operator = fallbackOperator;
    }

    return validatedCondition;
};

const getOperatorsForField = (field) => {
    if (field && field.operators) {
        return field.operators.map(op => ({
            value: op,
            label: getOperatorLabel(op)
        }));
    }

    if (field) {
        return props.operators[field.type] || props.operators.text;
    }

    return props.operators.text;
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

const initializeGroups = () => {
    if (props.value?.groups?.length) {
        return props.value.groups;
    }
    return [{
        conjunction: 'AND',
        conditions: []
    }];
};

const initializeLimit = () => {
    if (props.value?.limit) {
        return props.value.limit;
    }
    return props.defaultLimit;
};

const initializeSortField = () => {
    if (props.value?.sortField) {
        return props.value.sortField;
    }
    return props.defaultSortField;
};

const initializeSortDirection = () => {
    if (props.value?.sortDirection) {
        return props.value.sortDirection;
    }
    return props.defaultSortDirection;
};

const initializeUseDefaultQuery = () => {
    if (typeof props.value?.useDefaultQuery !== 'undefined') {
        return Boolean(props.value.useDefaultQuery);
    }
    return true;
};

const initializeBuilderTemplate = () => {
    if (props.value?.builderTemplate) {
        return props.value.builderTemplate;
    }
    return props.defaultBuilderTemplate;
};

const createDefaultGroup = () => {
    return {
        name: '',
        conjunction: 'AND',
        conditions: []
    };
};

const addGroup = () => {
    groups.value.push(createDefaultGroup());
    updateValue();
};

const insertGroupAt = (index) => {
    groups.value.splice(index, 0, createDefaultGroup());
    updateValue();
};

const duplicateGroup = (groupIndex) => {
    const group = groups.value[groupIndex];
    groups.value.splice(groupIndex + 1, 0, { ...group });
    updateValue();
};

const removeGroup = (groupIndex) => {
    groups.value.splice(groupIndex, 1);
    updateValue();
};

const updateGroup = (groupIndex, group) => {
    groups.value[groupIndex] = group;
    updateValue();
};

const moveGroupUp = (groupIndex) => {
    if (groupIndex > 0) {
        const group = groups.value[groupIndex];
        groups.value[groupIndex] = groups.value[groupIndex - 1];
        groups.value[groupIndex - 1] = group;
        updateValue();
    }
};

const moveGroupDown = (groupIndex) => {
    if (groupIndex < groups.value.length - 1) {
        const group = groups.value[groupIndex];
        groups.value[groupIndex] = groups.value[groupIndex + 1];
        groups.value[groupIndex + 1] = group;
        updateValue();
    }
};

const addConditionToGroup = (groupIndex) => {
    const condition = createDefaultCondition();
    groups.value[groupIndex].conditions.push(condition);
    updateValue();
};

const updateCondition = (groupIndex, conditionIndex, condition) => {
    groups.value[groupIndex].conditions[conditionIndex] = condition;
    updateValue();
};

const removeCondition = (groupIndex, conditionIndex) => {
    groups.value[groupIndex].conditions.splice(conditionIndex, 1);
    updateValue();
};

const addNestedGroupToGroup = (groupIndex) => {
    const nestedGroup = {
        type: 'group',
        ...createDefaultGroup()
    };
    groups.value[groupIndex].conditions.push(nestedGroup);
    updateValue();
};

const addConditionToNestedGroup = (groupIndex, nestedGroupIndex) => {
    const condition = createDefaultCondition();
    groups.value[groupIndex].conditions[nestedGroupIndex].conditions.push(condition);
    updateValue();
};

const addNestedGroupToNestedGroup = (groupIndex, nestedGroupIndex) => {
    const nestedGroup = {
        type: 'group',
        ...createDefaultGroup()
    };
    groups.value[groupIndex].conditions[nestedGroupIndex].conditions.push(nestedGroup);
    updateValue();
};

const updateNestedCondition = (groupIndex, nestedGroupIndex, conditionIndex, condition) => {
    groups.value[groupIndex].conditions[nestedGroupIndex].conditions[conditionIndex] = condition;
    updateValue();
};

const removeNestedCondition = (groupIndex, nestedGroupIndex, conditionIndex) => {
    groups.value[groupIndex].conditions[nestedGroupIndex].conditions.splice(conditionIndex, 1);
    updateValue();
};

const createDefaultCondition = () => {
    const firstField = flattenedFields.value[0];
    const defaultOperator = getOperatorsForField(firstField)[0]?.value || '=';
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
};

const updateValue = () => {
    const payload = {
        groups: groups.value,
        globalConjunction: globalConjunction.value,
        limit: limit.value,
        useDefaultQuery: useDefaultQuery.value,
        builderTemplate: builderTemplate.value,
        sortField: sortField.value,
        sortDirection: sortDirection.value,
    };
    emit('update:model-value', payload);
};

const updateSettingValues = () => {
    const currentValue = { ...props.value };
    currentValue.limit = parseInt(limit.value) || props.defaultLimit;
    currentValue.useDefaultQuery = Boolean(useDefaultQuery.value);
    currentValue.builderTemplate = builderTemplate.value || props.defaultBuilderTemplate;
    currentValue.sortField = sortField.value || props.defaultSortField;
    currentValue.sortDirection = sortDirection.value || props.defaultSortDirection;
    emit('update:model-value', currentValue);
};

onMounted(() => {
    groups.value = initializeGroups();
    limit.value = initializeLimit();
    sortField.value = initializeSortField();
    sortDirection.value = initializeSortDirection();
    builderTemplate.value = initializeBuilderTemplate();
    useDefaultQuery.value = initializeUseDefaultQuery();
    fetchPresets();
});
</script>

<style scoped>
.insert-group-btn {
    @apply flex items-center justify-center w-8 h-8 bg-blue-50 border-2 border-dashed border-blue-300 rounded-full text-blue-500 hover:bg-blue-100 hover:border-blue-400 transition-colors duration-200;
}

.insert-group-btn:hover {
    @apply shadow-sm;
}
</style>
