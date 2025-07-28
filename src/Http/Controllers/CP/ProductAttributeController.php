<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Statamic\Http\Controllers\CP\CpController;
use Rapidez\StatamicQueryBuilder\Models\ProductAttribute;

class ProductAttributeController extends CpController
{
    public function index()
    {
        return ProductAttribute::with('attributeOptions')->get();
    }
}
