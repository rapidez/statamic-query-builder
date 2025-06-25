<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\CpController;

class ProductAttributeController extends CpController
{
    public function index()
    {
        $model = config('rapidez.query-builder.models.ProductAttribute');

        if (! $model) {
            return [];
        }

        return $model::with('attributeOptions')->get();
    }
}
