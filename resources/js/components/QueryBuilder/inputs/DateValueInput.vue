<template>
    <div class="flex items-center space-x-2 flex-1">
        <Select
            :model-value="getDateValue().selectedOption"
            :options="getDateValueOptions()"
            :reduce="option => option.value"
            label="label"
            class="flex-1"
            :placeholder="__('Select date option')"
            @update:model-value="onDateOptionChange"
        />

        <template v-if="needsOffsetInput(getDateValue().selectedOption)">
            <Input
                type="number"
                :value="getDateValue().offset"
                class="input-text w-20"
                :placeholder="1"
                min="1"
                @input="updateDateOffset($event.target.value)"
            />
            <span class="text-sm font-medium text-gray-600">
                {{ getOffsetLabel(getDateValue().selectedOption) }}
            </span>
        </template>

        <Input
            v-if="getDateValue().selectedOption === 'OTHER'"
            type="date"
            :value="getDateValue().manualDate"
            class="input-text flex-1"
            @input="updateManualDate($event.target.value)"
        />
    </div>
</template>

<script setup>
import { Select, Input } from '@statamic/cms/ui';

const props = defineProps({
    condition: {
        type: Object,
        required: true
    },
    field: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update-value']);

const getDateValue = () => {
    const value = props.condition.value;

    if (!value || typeof value === 'string') {
        return {
            selectedOption: 'TODAY',
            offset: 1,
            manualDate: ''
        };
    }

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

    return {
        selectedOption: 'TODAY',
        offset: 1,
        manualDate: ''
    };
};

const getDateValueOptions = () => {
    return [
        { value: 'TODAY', label: __('Today') },
        { value: 'TOMORROW', label: __('Tomorrow') },
        { value: 'YESTERDAY', label: __('Yesterday') },
        { value: 'TODAY_PLUS_DAYS', label: __('Today + X days') },
        { value: 'TODAY_PLUS_WEEKS', label: __('Today + X weeks') },
        { value: 'TODAY_PLUS_MONTHS', label: __('Today + X months') },
        { value: 'TODAY_PLUS_YEARS', label: __('Today + X years') },
        { value: 'TODAY_MINUS_DAYS', label: __('Today - X days') },
        { value: 'TODAY_MINUS_WEEKS', label: __('Today - X weeks') },
        { value: 'TODAY_MINUS_MONTHS', label: __('Today - X months') },
        { value: 'TODAY_MINUS_YEARS', label: __('Today - X years') },
        { value: 'OTHER', label: __('Other...') }
    ];
};

const needsOffsetInput = (selectedOption) => {
    const offsetOptions = [
        'TODAY_PLUS_DAYS', 'TODAY_PLUS_WEEKS', 'TODAY_PLUS_MONTHS', 'TODAY_PLUS_YEARS',
        'TODAY_MINUS_DAYS', 'TODAY_MINUS_WEEKS', 'TODAY_MINUS_MONTHS', 'TODAY_MINUS_YEARS'
    ];
    return offsetOptions.includes(selectedOption);
};

const getOffsetLabel = (selectedOption) => {
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
};

const onDateOptionChange = (selectedOption) => {
    if (['TODAY', 'TOMORROW', 'YESTERDAY'].includes(selectedOption)) {
        emit('update-value', {
            type: 'relative',
            value: selectedOption
        });
    } else if (needsOffsetInput(selectedOption)) {
        const parts = selectedOption.split('_');
        const base = parts[0];
        const direction = parts[1];
        const unit = parts[2].toLowerCase();

        emit('update-value', {
            type: 'relative',
            base: base,
            offset: direction === 'MINUS' ? -1 : 1,
            unit: unit
        });
    } else if (selectedOption === 'OTHER') {
        emit('update-value', {
            type: 'manual',
            value: ''
        });
    }
};

const updateDateOffset = (offset) => {
    const currentValue = props.condition.value;
    if (currentValue.type === 'relative' && currentValue.base) {
        const direction = currentValue.offset >= 0 ? 1 : -1;
        emit('update-value', {
            ...currentValue,
            offset: direction * Math.abs(parseInt(offset) || 1)
        });
    }
};

const updateManualDate = (date) => {
    emit('update-value', {
        type: 'manual',
        value: date
    });
};
</script>
