<?php

namespace Rapidez\StatamicQueryBuilder;

use Rapidez\StatamicQueryBuilder\Fieldtypes\ProductQueryBuilder;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/statamic-query-builder.js'
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $fieldtypes = [
        ProductQueryBuilder::class,
    ];
}
