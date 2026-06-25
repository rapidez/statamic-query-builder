<?php

use Illuminate\Support\Facades\Route;
use Rapidez\StatamicQueryBuilder\Http\Controllers\CP\DefaultQueryController;
use Rapidez\StatamicQueryBuilder\Http\Controllers\CP\ProductAttributeController;
use Rapidez\StatamicQueryBuilder\Http\Controllers\CP\QueryPresetController;
use Rapidez\StatamicQueryBuilder\Http\Controllers\CP\SortingOptionsController;

Route::prefix('rapidez')->group(function () {
    Route::get('product-attributes', [ProductAttributeController::class, 'index'])->name('rapidez.product-attributes.index');
    Route::get('query-presets', [QueryPresetController::class, 'index'])->name('rapidez.query-presets.index');
    Route::get('sorting-options', [SortingOptionsController::class, 'index'])->name('rapidez.sorting-options.index');
    Route::get('default-query', [DefaultQueryController::class, 'index'])->name('rapidez.default-query.index');
    Route::post('default-query', [DefaultQueryController::class, 'store'])->name('rapidez.default-query.store');
});
