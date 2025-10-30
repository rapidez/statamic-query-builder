<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Statamic\Http\Controllers\CP\CpController;

class SortingOptionsController extends CpController
{
    public function index(): JsonResponse
    {
        $sorting = config('rapidez.searchkit.sorting', []);
        $normalized = collect($sorting)
            ->filter(function ($directions, $field) {
                return is_string($field) && $field !== '';
            })
            ->map(function ($directions, $field) {
                $label = ucwords(str_replace('_', ' ', strtolower($field)));

                return [
                    'label' => $label,
                    'value' => $field,
                ];
            })
            ->values()
            ->all();

        return response()->json($normalized);
    }
}
