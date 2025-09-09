<?php

use Rapidez\StatamicQueryBuilder\Models\ProductAttribute;
use Rapidez\StatamicQueryBuilder\Models\ProductAttributeOption;

return [
    'default_models' => [
        'product_attribute' => 'Rapidez\StatamicQueryBuilder\Models\ProductAttribute',
        'product_attribute_option' => 'Rapidez\StatamicQueryBuilder\Models\ProductAttributeOption',
    ],
    'models' => [
        ProductAttribute::class => [
            'name' => 'Product Attributes',
            'read_only' => true,
            'title_field' => 'frontend_label',
            'listing' => [
                'columns' => [
                    'attribute_code',
                    'frontend_label',
                    'frontend_input',
                    'is_filterable',
                    'is_searchable',
                    'formatted_options',
                ],
                'sort' => [
                    'field' => 'attribute_code',
                    'direction' => 'asc',
                ],
            ],
            'search' => [
                'fields' => [
                    'attribute_code',
                    'frontend_label',
                    'store_frontend_label',
                    'option_values',
                    'frontend_input',
                ],
            ],
        ],
        ProductAttributeOption::class => [
            'name' => 'Product Attribute Options',
            'read_only' => true,
            'title_field' => 'display_value',
            'listing' => [
                'columns' => [
                    'option_id',
                    'attribute_code',
                    'attribute_label',
                    'admin_value',
                    'store_value',
                    'sort_order',
                ],
                'sort' => [
                    'field' => 'sort_order',
                    'direction' => 'asc',
                ],
            ],
            'search' => [
                'fields' => [
                    'option_id',
                    'attribute_code',
                    'attribute_label',
                    'admin_value',
                    'store_value',
                ],
            ],
        ],
    ],

    'preset_files' => [
        'vendor/rapidez/statamic-query-builder/resources/presets/stock-management.json',
        'vendor/rapidez/statamic-query-builder/resources/presets/product-visibility.json',
        'vendor/rapidez/statamic-query-builder/resources/presets/pricing-filters.json',
    ],
];
