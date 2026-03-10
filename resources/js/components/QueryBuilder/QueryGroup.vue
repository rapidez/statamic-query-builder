<template>
    <div :class="[
        'border rounded-lg p-4',
        isNested ? 'border-blue-200 bg-blue-50/30' : 'border-gray-300'
    ]">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span v-if="isNested" class="text-blue-600">⚬</span>
                    <h3 :class="[
                        'font-bold',
                        isNested ? 'text-sm text-blue-700' : 'text-base'
                    ]">
                        <Input
                            type="text"
                            :value="localGroup.name"
                            class="input-text"
                            :placeholder="(isNested ? __('Nested Group') : __('Group')) + ' ' + displayIndex"
                            @input="updateName"
                        />
                    </h3>
                    <Button
                        v-if="hasConditions"
                        @click="toggleCollapsed"
                        class="text-blue-600 hover:text-blue-800"
                        :title="isCollapsed ? __('Expand') : __('Collapse')"
                    >
                        {{ isCollapsed ? '▶' : '▼' }}
                    </Button>
                </div>
                <Select
                    :model-value="localGroup.conjunction"
                    :options="logicalOperators"
                    class="w-32"
                    @update:model-value="updateConjunction"
                />
            </div>
            <div class="flex items-center space-x-2">
                <Select
                    :options="addOptions"
                    :reduce="option => option.value"
                    label="label"
                    :placeholder="__('Add...')"
                    class="w-40"
                    @update:model-value="handleAddSelection"
                >
                </Select>
                <div class="flex items-center space-x-1">
                    <Button
                        v-if="canMoveUp"
                        class="btn p-2"
                        @click="$emit('move-group-up', groupIndex)"
                        :title="__('Move group up')"
                    >
                        ↑
                    </Button>
                    <Button
                        v-if="canMoveDown"
                        class="btn p-2"
                        @click="$emit('move-group-down', groupIndex)"
                        :title="__('Move group down')"
                    >
                        ↓
                    </Button>
                </div>
                <Button
                    v-if="canRemove"
                    class="btn-danger"
                    @click="$emit('remove-group', groupIndex)"
                >
                    {{ __('Remove Group') }}
                </Button>
                <Button
                    v-if="canDuplicate"
                    class="btn-primary"
                    @click="$emit('duplicate-group', groupIndex)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </Button>
            </div>
        </div>

        <div v-if="isCollapsed && hasConditions" class="text-sm text-gray-600">
            {{ getCollapsedSummary() }}
        </div>

        <div v-if="!isCollapsed" class="space-y-3">
            <template v-for="(item, itemIndex) in localGroup.conditions" :key="getItemKey(item, itemIndex)">
                <query-condition
                    v-if="!item.type || item.type !== 'group'"
                    :condition="item"
                    :condition-index="itemIndex"
                    :group-index="groupIndex"
                    :fields="fields"
                    :operators="operators"
                    @update-condition="updateCondition"
                    @remove-condition="removeCondition"
                />

                <query-group
                    v-else
                    :group="item"
                    :group-index="itemIndex"
                    :parent-group-index="groupIndex"
                    :fields="fields"
                    :operators="operators"
                    :is-nested="true"
                    :display-index="getNestedGroupIndex(itemIndex) + 1"
                    :can-move-up="itemIndex > 0"
                    :can-move-down="itemIndex < localGroup.conditions.length - 1"
                    :can-remove="true"
                    @update-group="updateNestedGroup"
                    @remove-group="removeNestedGroup"
                    @duplicate-group="duplicateNestedGroup"
                    @move-group-up="moveNestedGroupUp"
                    @move-group-down="moveNestedGroupDown"
                    @add-condition="emitEvent('add-condition-to-nested', $event)"
                    @add-nested-group="emitEvent('add-nested-group-to-nested', $event)"
                    @update-condition="emitEvent('update-nested-condition', $event)"
                    @remove-condition="emitEvent('remove-nested-condition', $event)"
                />
            </template>
        </div>

        <div v-if="!hasConditions" class="text-center py-4 text-gray-500 text-sm">
            {{ __('No conditions added yet') }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import QueryCondition from './QueryCondition.vue';
import { Select, Input, Button } from '@statamic/cms/ui';

const props = defineProps({
    group: {
        type: Object,
        required: true
    },
    groupIndex: {
        type: Number,
        required: true
    },
    parentGroupIndex: {
        type: Number,
        default: null
    },
    fields: {
        type: Array,
        required: true
    },
    operators: {
        type: Object,
        required: true
    },
    isNested: {
        type: Boolean,
        default: false
    },
    displayIndex: {
        type: [Number, String],
        default: null
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
    },
    canDuplicate: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits([
    'update-group',
    'remove-group',
    'duplicate-group',
    'move-group-up',
    'move-group-down',
    'add-condition',
    'add-nested-group',
    'add-condition-to-nested',
    'add-nested-group-to-nested',
    'update-condition',
    'update-nested-condition',
    'remove-condition',
    'remove-nested-condition'
]);

const logicalOperators = ['AND', 'OR'];
const isCollapsed = ref(false);
const localGroup = ref({ ...props.group });

watch(() => props.group, (newGroup) => {
    localGroup.value = { ...newGroup };
}, { deep: true });

const addOptions = computed(() => [
    { label: __('Add Condition'), value: 'condition' },
    { label: __('Add Nested Group'), value: 'nested-group' }
]);

const hasConditions = computed(() => {
    return localGroup.value.conditions && localGroup.value.conditions.length > 0;
});

const actualDisplayIndex = computed(() => {
    return props.displayIndex !== null ? props.displayIndex : props.groupIndex + 1;
});

const handleAddSelection = (value) => {
    emit(value === 'condition' ? 'add-condition' : 'add-nested-group', props.groupIndex);
};

const toggleCollapsed = () => {
    isCollapsed.value = !isCollapsed.value;
};

const getCollapsedSummary = () => {
    const regularConditions = localGroup.value.conditions.filter(item => !item.type || item.type !== 'group');
    const nestedGroups = localGroup.value.conditions.filter(item => item.type === 'group');

    const parts = [];
    if (regularConditions.length > 0) {
        parts.push(`${regularConditions.length} condition${regularConditions.length !== 1 ? 's' : ''}`);
    }
    if (nestedGroups.length > 0) {
        parts.push(`${nestedGroups.length} nested group${nestedGroups.length !== 1 ? 's' : ''}`);
    }

    return parts.join(', ') || __('No conditions');
};

const getItemKey = (item, index) => {
    if (item.type === 'group') {
        return `nested-group-${index}`;
    }
    return `condition-${index}`;
};

const getNestedGroupIndex = (itemIndex) => {
    let nestedGroupCount = 0;
    for (let i = 0; i < itemIndex; i++) {
        if (localGroup.value.conditions[i].type === 'group') {
            nestedGroupCount++;
        }
    }
    return nestedGroupCount;
};

const updateName = (e) => {
    localGroup.value.name = e.target.value;
    emit('update-group', props.groupIndex, { ...localGroup.value });
};

const updateConjunction = (conjunction) => {
    localGroup.value = { ...localGroup.value, conjunction };
    emit('update-group', props.groupIndex, { ...localGroup.value });
};

const updateCondition = (conditionIndex, condition) => {
    emit('update-condition', props.groupIndex, conditionIndex, condition);
};

const removeCondition = (conditionIndex) => {
    emit('remove-condition', props.groupIndex, conditionIndex);
};

const updateNestedGroup = (nestedGroupIndex, nestedGroup) => {
    const updatedGroup = { ...localGroup.value };
    updatedGroup.conditions[nestedGroupIndex] = { ...nestedGroup, type: 'group' };
    emit('update-group', props.groupIndex, updatedGroup);
};

const removeNestedGroup = (nestedGroupIndex) => {
    const updatedGroup = { ...localGroup.value };
    updatedGroup.conditions.splice(nestedGroupIndex, 1);
    emit('update-group', props.groupIndex, updatedGroup);
};

const duplicateNestedGroup = (nestedGroupIndex) => {
    const updatedGroup = { ...localGroup.value };
    const item = updatedGroup.conditions[nestedGroupIndex];
    updatedGroup.conditions.splice(nestedGroupIndex + 1, 0, { ...item, type: 'group' });
    emit('update-group', props.groupIndex, updatedGroup);
};

const moveNestedGroupUp = (nestedGroupIndex) => {
    if (nestedGroupIndex > 0) {
        const updatedGroup = { ...localGroup.value };
        const item = updatedGroup.conditions[nestedGroupIndex];
        updatedGroup.conditions[nestedGroupIndex] = updatedGroup.conditions[nestedGroupIndex - 1];
        updatedGroup.conditions[nestedGroupIndex - 1] = item;
        emit('update-group', props.groupIndex, updatedGroup);
    }
};

const moveNestedGroupDown = (nestedGroupIndex) => {
    if (nestedGroupIndex < localGroup.value.conditions.length - 1) {
        const updatedGroup = { ...localGroup.value };
        const item = updatedGroup.conditions[nestedGroupIndex];
        updatedGroup.conditions[nestedGroupIndex] = updatedGroup.conditions[nestedGroupIndex + 1];
        updatedGroup.conditions[nestedGroupIndex + 1] = item;
        emit('update-group', props.groupIndex, updatedGroup);
    }
};

const emitEvent = (eventName, ...args) => {
    emit(eventName, props.groupIndex, ...args);
};
</script>
