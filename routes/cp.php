<?php

use Illuminate\Support\Facades\Route;
use Rapidez\StatamicQueryBuilder\Http\Controllers\CP\ProductAttributeController;

Route::prefix('rapidez')->group(function () {
    Route::get('product-attributes', [ProductAttributeController::class, 'index'])->name('rapidez.product-attributes.index');
});
