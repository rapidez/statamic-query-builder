<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Rapidez\StatamicQueryBuilder\Support\SortingOptions;
use Statamic\Http\Controllers\CP\CpController;

class SortingOptionsController extends CpController
{
    public function index(): JsonResponse
    {
        return response()->json(SortingOptions::forControlPanel());
    }
}
