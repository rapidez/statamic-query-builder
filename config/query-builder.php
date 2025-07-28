<?php

use Rapidez\StatamicQueryBuilder\Models\ProductAttribute;
use Rapidez\StatamicQueryBuilder\Models\ProductAttributeOption;

return [
    'models' => [
        ProductAttribute::class => [
            'name' => 'Product Attributes',
            'read_only' => true,
            'title_field' => 'frontend_label',
            'cp_icon' => 'tags',
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
            'cp_icon' => 'list-bullets',
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
    ]
];
