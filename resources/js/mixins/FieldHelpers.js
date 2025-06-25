export default {
    methods: {
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
            const field = this.fields.find(field => field.value === (condition?.attribute));
            return field?.type === 'select' && ['IN', 'NOT IN'].includes(condition?.operator);
        },

        isSingleSelectVisible(condition) {
            const field = this.fields.find(field => field.value === (condition?.attribute));
            return field?.type === 'select' && !['IN', 'NOT IN'].includes(condition?.operator);
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
            const field = this.fields.find(field => field.value === fieldName);
            return field ? this.operators[field.type] || [] : [];
        },

        initializeGroups() {
            if (this.value?.groups?.length) {
                return this.value.groups;
            }
            return [{
                conjunction: 'AND',
                conditions: []
            }];
        }
    }
}
