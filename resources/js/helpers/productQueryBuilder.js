import axios from 'axios';

export const FIELD_GROUPS = [
    {
        key: 'attribute',
        label: 'Attributes',
        prefix: 'attribute.'
    },
    {
        key: 'stock',
        label: 'Stock',
        prefix: '',
        fields: [
            {
                label: 'Stock Status',
                value: 'stock_status',
                type: 'select',
                operators: ['=', '!='],
                options: [
                    { label: 'In Stock', value: 'in_stock' },
                    { label: 'Out of Stock', value: 'out_of_stock' }
                ]
            }
        ]
    }
];

const mapAttributeType = (frontendInput) => {
    const typeMap = {
        'text': 'text',
        'textarea': 'text',
        'select': 'select',
        'multiselect': 'select',
        'boolean': 'select',
        'price': 'number',
        'weight': 'number',
        'date': 'date',
        'datetime': 'date',
        'timestamp': 'date'
    };
    return typeMap[frontendInput] || 'text';
};

const mapAttributeOptions = (options) => {
    if (!options || !options.length) {
        return [];
    }
    const opts = typeof options === 'object' && !Array.isArray(options) ? Object.values(options) : options;
    return opts.map((option) => ({
        label: option.store_value || option.option_id,
        value: option.store_value || option.option_id
    }));
};

export const buildGroupedFields = (attributes, fieldGroups = FIELD_GROUPS) => {
    return fieldGroups.map((group) => {
        if (group.key === 'attribute') {
            return {
                label: group.label,
                options: attributes
            };
        }
        return {
            label: group.label,
            options: group.fields.map((field) => ({
                ...field,
                value: group.prefix ? `${group.prefix}${field.value}` : field.value
            }))
        };
    });
};

const transformAttributeToField = (attr) => ({
    label: attr.frontend_label ? (attr.frontend_label + ` (${attr.attribute_code})`) : attr.attribute_code,
    value: `attribute.${attr.attribute_code}`,
    type: mapAttributeType(attr.input),
    options: mapAttributeOptions(attr.attribute_options)
});

export const fetchProductAttributes = async () => {
    const response = await axios.get('/cp/rapidez/product-attributes');
    return response.data.map((attr) => transformAttributeToField(attr));
};

export const fetchSortingOptions = async () => {
    try {
        const response = await axios.get('/cp/rapidez/sorting-options');
        const options = Array.isArray(response.data) ? response.data : [];

        if (!options.length) {
            return [];
        }

        return options.filter((option) => option && option.value);
    } catch (error) {
        console.error('Error fetching sorting options:', error);
        return [
            { label: 'Newest', value: 'created_at' },
            { label: 'Oldest', value: 'created_at' }
        ];
    }
};

export const normalizeQuery = (query) => {
    if (!query || typeof query !== 'object') {
        return { groups: [{ conjunction: 'AND', conditions: [] }], globalConjunction: 'AND' };
    }
    const groups = Array.isArray(query.groups) && query.groups.length > 0
        ? query.groups
        : [{ conjunction: 'AND', conditions: [] }];
    return {
        groups,
        globalConjunction: query.globalConjunction || 'AND'
    };
};
