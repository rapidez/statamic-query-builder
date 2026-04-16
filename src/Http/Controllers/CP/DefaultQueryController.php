<?php

namespace Rapidez\StatamicQueryBuilder\Http\Controllers\CP;

use Inertia\Inertia;
use Rapidez\StatamicQueryBuilder\Http\Requests\DefaultQueryRequest;
use Rapidez\StatamicQueryBuilder\Services\DefaultQueryService;
use Statamic\Http\Controllers\CP\CpController;

class DefaultQueryController extends CpController
{
    public function __construct(
        protected DefaultQueryService $defaultQueryService
    ) {}

    public function index()
    {
        $settings = $this->defaultQueryService->getDefaultQuerySettings();

        return Inertia::render('statamic-query-builder::DefaultPreset', [
            'enabled' => $settings['enabled'],
            'query' => $settings['query'],
            'saveUrl' => cp_route('rapidez.default-query.store'),
        ]);
    }

    public function store(DefaultQueryRequest $request)
    {
        $validated = $request->validated();

        $query = [
            'groups' => $validated['query']['groups'] ?? [],
            'globalConjunction' => $validated['query']['globalConjunction'] ?? 'AND',
        ];

        $this->defaultQueryService->saveDefaultQuerySettings([
            'enabled' => $validated['enabled'],
            'query' => $query,
        ]);

        return back()->with('success', __('Default query saved successfully.'));
    }
}
