<?php

use Rapidez\StatamicQueryBuilder\Models\ProductAttribute;
use Rapidez\StatamicQueryBuilder\Models\ProductAttributeOption;

return [
    'default_limit' => 100,

    'default_query' => [
        'enabled' => true,
        'query' => [
            'groups' => [
                [
                    'conjunction' => 'AND',
                    'conditions' => [
                        [
                            'attribute' => 'attribute.visibility',
                            'operator' => '!=',
                            'value' => '1',
                        ],
                        [
                            'attribute' => 'stock_status',
                            'operator' => '=',
                            'value' => 'in_stock',
                        ],
                    ],
                ],
            ],
            'globalConjunction' => 'AND',
        ],
    ],
    'default_models' => [
        'product_attribute' => 'Rapidez\StatamicQueryBuilder\Models\ProductAttribute',
        'product_attribute_option' => 'Rapidez\StatamicQueryBuilder\Models\ProductAttributeOption',
    ],
    'models' => [
        ProductAttribute::class => [
            'name' => 'Product Attributes',
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

    /*
      Shown in the Product Query Builder CP sort dropdown. Applied per
      productlist/listing instance via config-callback (not via
      rapidez.searchkit.sorting), so category listing SortBy stays clean.

      - cast: cast the root field when indexing (float|int)
      - index_attribute: ensure Magento attribute gets a typed ES mapping
    */
    'sorting_options' => [
        [
            'label' => 'most_viewed',
            'value' => 'popularity',
            'field' => 'popularity',
        ],
        [
            'label' => 'most_sold',
            'value' => 'sales_factor',
            'field' => 'sales_factor',
            'cast' => 'float',
            'index_attribute' => true,
        ],
        [
            'label' => 'most_reviews',
            'value' => 'review_summary.reviews_count',
            'field' => 'review_summary.reviews_count',
        ],
        [
            'label' => 'best_review_score',
            'value' => 'review_summary.rating_summary',
            'field' => 'review_summary.rating_summary',
        ],
    ],
];
