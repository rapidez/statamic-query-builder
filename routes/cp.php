<?php

use Illuminate\Support\Facades\Route;
use Rapidez\StatamicQueryBuilder\Http\Controllers\CP\ProductAttributeController;
use Rapidez\StatamicQueryBuilder\Http\Controllers\CP\QueryPresetController;

Route::prefix('rapidez')->group(function () {
    Route::get('product-attributes', [ProductAttributeController::class, 'index'])->name('rapidez.product-attributes.index');
    Route::get('query-presets', [QueryPresetController::class, 'index'])->name('rapidez.query-presets.index');
});
