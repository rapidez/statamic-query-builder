<?php

namespace Rapidez\StatamicQueryBuilder\Observers;

use Illuminate\Support\Facades\Cache;
use Rapidez\StatamicQueryBuilder\Models\ProductAttribute;
use Rapidez\StatamicQueryBuilder\Models\ProductAttributeOption;

class ProductAttributeObserver
{
    public function saved(ProductAttribute|ProductAttributeOption $model): void
    {
        Cache::forget($model->getCacheKey());
    }

    public function deleted(ProductAttribute|ProductAttributeOption $model): void
    {
        Cache::forget($model->getCacheKey());
    }
}
