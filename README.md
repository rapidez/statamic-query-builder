# Statamic Query Builder

A sophisticated visual query builder for Statamic CMS that generates Elasticsearch DSL queries for complex product filtering and search. Built specifically for e-commerce applications using the Rapidez framework.

## Features

### Visual Query Building
- **Drag & drop interface** - Build complex queries without writing code
- **Nested group logic** - Create groups within groups with AND/OR operators
- **Collapsible groups** - Manage complex queries with expandable sections
- **Visual condition builder** - Point-and-click interface for all query operations

### E-commerce Focused
- **Product attribute integration** - Automatically fetches and maps product attributes
- **Stock status queries** - Built-in support for inventory filtering
- **Elasticsearch DSL generation** - Converts visual queries to optimized search syntax
- **Performance caching** - Cached query generation for better performance

### Advanced Field Types
- **Text fields** - String operations (contains, starts with, ends with, etc.)
- **Select fields** - Single and multi-select with custom options
- **Number fields** - Numeric comparisons and ranges
- **Date fields** - Advanced date filtering with relative and absolute dates
- **Stock status** - Specialized e-commerce inventory filtering

### Powerful Date Operations
- **Relative dates** - TODAY, TOMORROW, YESTERDAY
- **Dynamic ranges** - LAST_X_DAYS, NEXT_X_DAYS with custom offsets
- **Period filters** - THIS_WEEK, THIS_MONTH, THIS_YEAR
- **Manual dates** - Custom date picker for specific dates
- **Offset calculations** - Today ± X days/weeks/months/years

### Query Management
- **Query presets** - Save and load common filtering scenarios
- **Template system** - Multiple output formats (slider, listing, etc.)
- **Import/export** - Share query configurations
- **Validation** - Automatic validation and correction of imported queries

### Professional Features
- **Unlimited nesting** - Groups within groups with no limits
- **Reorderable conditions** - Drag conditions and groups to reorder
- **Duplicate functionality** - Clone groups and conditions
- **Conflict resolution** - Smart handling of preset merging vs overriding

## Installation

Install via Composer:

```bash
composer require rapidez/statamic-query-builder
```

Publish the configuration and views:

```bash
php artisan vendor:publish --provider="Rapidez\StatamicQueryBuilder\ServiceProvider"
```

## Quick Start

### Basic Product Query Builder

The addon comes with a pre-configured `ProductQueryBuilder` fieldtype for immediate use:

```yaml
# In your blueprint
fields:
  product_filter:
    type: product_query_builder
    display: Product Filter
    instructions: Build complex product queries visually
```

### Custom Query Builder

Create a custom query builder for your specific needs:

```vue
<template>
    <query-builder
        :fields="groupedFields"
        :sort-fields="sortFields"
        :default-limit="50"
        :show-limit="true"
        :builder-templates="templates"
        v-model="value"
        @input="$emit('input', $event)"
    />
</template>

<script>
import QueryBuilder from './QueryBuilder.vue';

export default {
    components: { QueryBuilder },
    mixins: [Fieldtype],
    
    data() {
        return {
            groupedFields: [
                {
                    label: 'Product Attributes',
                    options: [
                        {
                            label: 'Product Name',
                            value: 'name',
                            type: 'text'
                        },
                        {
                            label: 'Category',
                            value: 'category',
                            type: 'select',
                            options: [
                                { label: 'Electronics', value: 'electronics' },
                                { label: 'Clothing', value: 'clothing' }
                            ]
                        },
                        {
                            label: 'Price',
                            value: 'price',
                            type: 'number'
                        },
                        {
                            label: 'Created Date',
                            value: 'created_at',
                            type: 'date'
                        }
                    ]
                },
                {
                    label: 'Stock Information',
                    options: [
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
            ],
            templates: [
                { label: 'Product Slider', value: 'slider' },
                { label: 'Product Grid', value: 'listing' }
            ]
        }
    }
}
</script>
```

## Supported Field Types & Operators

### Text Fields
- `=` - Exactly equals
- `!=` - Does not equal
- `LIKE` - Contains text
- `NOT LIKE` - Does not contain
- `STARTS_WITH` - Begins with
- `ENDS_WITH` - Ends with
- `IS_NULL` - Field is empty
- `IS_NOT_NULL` - Field has value

### Select Fields
- `=` - Equals selected value
- `!=` - Does not equal
- `IN` - Is any of (multiple selection)
- `NOT IN` - Is none of (multiple selection)
- `IS_NULL` - Nothing selected
- `IS_NOT_NULL` - Has selection

### Number Fields
- `=`, `!=` - Equality comparisons
- `>`, `<`, `>=`, `<=` - Numeric comparisons
- `BETWEEN` - Value within range
- `NOT_BETWEEN` - Value outside range
- `IS_NULL`, `IS_NOT_NULL` - Empty/has value

### Date Fields
- `=`, `!=`, `>`, `<`, `>=`, `<=` - Date comparisons
- `BETWEEN`, `NOT_BETWEEN` - Date ranges
- `LAST_X_DAYS`, `NEXT_X_DAYS` - Relative ranges
- `THIS_WEEK`, `THIS_MONTH`, `THIS_YEAR` - Period filters
- `IS_NULL`, `IS_NOT_NULL` - Date set/not set

## Advanced Date Filtering

The query builder includes sophisticated date handling:

### Relative Dates
```javascript
// Simple relative dates
{ type: 'relative', value: 'TODAY' }
{ type: 'relative', value: 'YESTERDAY' }
{ type: 'relative', value: 'TOMORROW' }

// Dynamic relative dates with offsets
{
    type: 'relative',
    base: 'TODAY',
    offset: -7,
    unit: 'days'
}
```

### Manual Dates
```javascript
{
    type: 'manual',
    value: '2024-01-15'
}
```

## Query Presets

Create reusable query templates:

```json
{
    "category": {
        "key": "products",
        "label": "Product Queries"
    },
    "presets": [
        {
            "key": "featured_products",
            "name": "Featured Products",
            "description": "Products marked as featured",
            "query": {
                "groups": [
                    {
                        "conjunction": "AND",
                        "conditions": [
                            {
                                "attribute": "featured",
                                "operator": "=",
                                "value": "1"
                            }
                        ]
                    }
                ],
                "globalConjunction": "AND"
            }
        }
    ]
}
```

Configure preset files in your config:

```php
// config/rapidez/query-builder.php
return [
    'preset_files' => [
        'resources/query-presets/products.json',
        'resources/query-presets/categories.json',
    ]
];
```

## Output Format

The query builder generates structured output perfect for Elasticsearch:

```json
{
    "groups": [
        {
            "conjunction": "AND",
            "conditions": [
                {
                    "attribute": "attribute.brand",
                    "operator": "IN",
                    "value": ["nike", "adidas"]
                },
                {
                    "attribute": "price",
                    "operator": "BETWEEN",
                    "value": ["100", "500"]
                }
            ]
        }
    ],
    "globalConjunction": "AND",
    "limit": 50,
    "sortField": "created_at",
    "sortDirection": "DESC",
    "builderTemplate": "listing"
}
```

This gets automatically converted to Elasticsearch DSL:

```json
{
    "query": {
        "bool": {
            "must": [
                {
                    "terms": {
                        "attribute.brand.keyword": ["nike", "adidas"]
                    }
                },
                {
                    "range": {
                        "price": {
                            "gte": 100,
                            "lte": 500
                        }
                    }
                }
            ]
        }
    },
    "size": 50,
    "from": 0
}
```

## Templates

The chosen template is used to render the results of the query. You can include the right template in the blade template of your page builder item:
```blade
@include('rapidez-query-builder::templates.'. $product_query_builder->value()['builderTemplate'], $product_query_builder->value())
```

## Configuration

### Product Attribute Model

Configure your product attribute model:

```php
// config/rapidez/query-builder.php
return [
    'models' => [
        'product_attribute' => App\Models\ProductAttribute::class,
    ]
];
```

### Field Mappings

The addon automatically maps Elasticsearch field types:

- `text` fields → `.keyword` for exact matches
- `stock_status` → `in_stock` in Elasticsearch
- Attribute fields → `attribute.{attribute_code}` prefix

## Requirements

- PHP ^8.0
- Laravel ^9.0|^10.0|^11.0
- Statamic ^4.0|^5.0
- Rapidez framework
- Elasticsearch (via [Rapidez Laravel Elasticsearch package](https://github.com/rapidez/laravel-scout-elasticsearch))

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
