<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\CpController;

class ProductAttributeController extends CpController
{
    public function index(): array
    {
        $model = config('rapidez.query-builder.default_models.product_attribute');

        if (! $model) {
            return [];
        }

        return $model::with('attributeOptions')->get();
    }
}
