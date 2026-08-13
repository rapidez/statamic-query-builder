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

        return $model::with('attributeOptions')
            ->whereIn('eav_attribute.attribute_id', function ($query) {
                $query->select('attribute_id')
                    ->from('catalog_eav_attribute')
                    ->where('is_filterable', '>', 0);
            })
            ->get();
    }
}
