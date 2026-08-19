<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\CpController;

class ProductAttributeController extends CpController
{
    public function index()
    {
        $model = config('rapidez.query-builder.default_models.product_attribute');
        if (! $model) {
            return [];
        }

        $query = $model::with('attributeOptions');

        // Pick the correct filter strategy for the configured model.
        if (is_a($model, \Rapidez\StatamicQueryBuilder\Models\ProductAttribute::class, true)) {
            $query->filterable();
        } else {
            $query->whereIn('eav_attribute.attribute_id', function ($subQuery) {
                $subQuery->select('attribute_id')
                    ->from('catalog_eav_attribute')
                    ->where('is_filterable', '>', 0);
            });
        }

        return $query->get();
    }
}
