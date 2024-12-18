# Statamic Query Builder

A flexible and reusable query builder component for Statamic that allows you to create complex queries for any type of data.

## Features

- Generic query builder component that can be used with any data type
- Support for multiple field types (text, select, number, date)
- Customizable operators per field type
- Group conditions with AND/OR logic
- Limit results
- Easy to extend for specific data types

## Installation

You can install this addon via Composer:

```bash
composer require rapidez/statamic-query-builder
```

## Usage

### Basic Usage

The query builder comes with a base component that can be used with any data type. Here's how to use it:

```vue
<query-builder
    :fields="[
        {
            label: 'Name',
            value: 'name',
            type: 'text'
        },
        {
            label: 'Status',
            value: 'status',
            type: 'select',
            options: [
                { label: 'Active', value: 'active' },
                { label: 'Inactive', value: 'inactive' }
            ]
        }
    ]"
    v-model="queryValue"
/>
```

### Creating a Custom Query Builder

To create a query builder for your specific data type, extend the base component. Here's an example:

```vue
<!-- CustomQueryBuilder.vue -->
<template>
    <query-builder
        :fields="fields"
        :operators="operators"
        :default-limit="100"
        :show-limit="true"
        v-model="value"
        @input="$emit('input', $event)"
    />
</template>

<script>
import QueryBuilder from './QueryBuilder.vue';

export default {
    components: {
        QueryBuilder
    },

    mixins: [Fieldtype],

    data() {
        return {
            fields: [],
            operators: {
                text: ['=', '!=', 'LIKE', 'NOT LIKE', 'STARTS_WITH', 'ENDS_WITH', 'IS_NULL', 'IS_NOT_NULL'],
                select: ['=', '!=', 'IN', 'NOT IN', 'IS_NULL', 'IS_NOT_NULL'],
                number: ['=', '!=', '>', '<', '>=', '<=', 'BETWEEN', 'NOT_BETWEEN', 'IS_NULL', 'IS_NOT_NULL'],
                date: ['=', '!=', '>', '<', '>=', '<=', 'BETWEEN', 'NOT_BETWEEN', 'LAST_X_DAYS', 'NEXT_X_DAYS', 'THIS_WEEK', 'THIS_MONTH', 'THIS_YEAR', 'IS_NULL', 'IS_NOT_NULL']
            }
        }
    },

    methods: {
        async fetchFields() {
            // Implement your field fetching logic here
            this.fields = [
                {
                    label: 'Name',
                    value: 'name',
                    type: 'text'
                },
                // ... more fields
            ];
        }
    },

    mounted() {
        this.fetchFields();
    }
}
</script>
```

### Field Configuration

Each field in the fields array should have the following structure:

```typescript
interface Field {
    label: string;      // Display name
    value: string;      // Internal identifier
    type: string;       // Field type (text, select, number, date)
    options?: Option[]; // Required for select fields
}

interface Option {
    label: string;
    value: string | number;
}
```

### Supported Field Types

- `text`: Text input with string operations
- `select`: Single/multi-select with options
- `number`: Numeric input with comparison operations
- `date`: Date input with date-specific operations

### Available Operators

The component supports different operators based on field type:

- Text:
  - `=`: Equals
  - `!=`: Not equals
  - `LIKE`: Contains
  - `NOT LIKE`: Does not contain
  - `STARTS_WITH`: Begins with text
  - `ENDS_WITH`: Ends with text
  - `IS_NULL`: Field is empty
  - `IS_NOT_NULL`: Field is not empty

- Select:
  - `=`: Equals
  - `!=`: Not equals
  - `IN`: In list
  - `NOT IN`: Not in list
  - `IS_NULL`: No option selected
  - `IS_NOT_NULL`: Has option selected

- Number:
  - `=`: Equals
  - `!=`: Not equals
  - `>`: Greater than
  - `<`: Less than
  - `>=`: Greater than or equal
  - `<=`: Less than or equal
  - `BETWEEN`: Value is between two numbers
  - `NOT_BETWEEN`: Value is not between two numbers
  - `IS_NULL`: Field is empty
  - `IS_NOT_NULL`: Field is not empty

- Date:
  - `=`: Equals
  - `!=`: Not equals
  - `>`: After
  - `<`: Before
  - `>=`: After or on
  - `<=`: Before or on
  - `BETWEEN`: Date is between two dates
  - `NOT_BETWEEN`: Date is not between two dates
  - `LAST_X_DAYS`: Within the last X days
  - `NEXT_X_DAYS`: Within the next X days
  - `THIS_WEEK`: Current week
  - `THIS_MONTH`: Current month
  - `THIS_YEAR`: Current year
  - `IS_NULL`: Date is not set
  - `IS_NOT_NULL`: Date is set

## Output Format

The query builder outputs data in the following format:

```json
{
    "groups": [
        {
            "conjunction": "AND",
            "conditions": [
                {
                    "attribute": "status",
                    "operator": "=",
                    "value": "active"
                }
            ]
        }
    ],
    "globalConjunction": "AND",
    "limit": 100
}
