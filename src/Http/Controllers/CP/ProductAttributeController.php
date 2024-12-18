<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Rapidez\Statamic\Models\ProductAttribute;
use Statamic\Http\Controllers\CP\CpController;

class ProductAttributeController extends CpController
{
    public function index()
    {
        return ProductAttribute::with('attributeOptions')->get();
    }
}
