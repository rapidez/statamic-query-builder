<?php

namespace Rapidez\StatamicQueryBuilder;

use Rapidez\StatamicQueryBuilder\Actions\OutputsDslQueryAction;
use Rapidez\StatamicQueryBuilder\Fieldtypes\ProductQueryBuilder;
use Statamic\Facades\CP\Nav;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    /**
     * @var array{input: list<string>, publicDirectory: string}
     */
    protected $vite = [
        'input' => [
            'resources/js/statamic-query-builder.js',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $fieldtypes = [
        ProductQueryBuilder::class,
    ];

    public function bootAddon(): void
    {
        $this->app->singleton(OutputsDslQueryAction::class);

        $this->bootConfig()
            ->bootModels()
            ->bootNavigation();
    }

    protected function bootConfig(): self
    {
        parent::bootConfig();

        $this->mergeConfigFrom(__DIR__.'/../config/query-builder.php', 'rapidez.query-builder');
        $this->publishes([
            __DIR__.'/../config/query-builder.php' => config_path('rapidez/query-builder.php'),
        ], 'rapidez-query-builder-config');

        return $this;
    }

    protected function bootViews(): self
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rapidez-query-builder');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rapidez-query-builder'),
        ], 'rapidez-query-builder-views');

        return $this;
    }

    protected function bootModels(): self
    {
        config(['runway.resources' => array_merge(
            config('rapidez.query-builder.models') ?? [],
            config('runway.resources') ?? []
        )]);

        return $this;
    }

    protected function bootNavigation(): self
    {
        Nav::extend(
            function ($nav) {
                $nav->content('Default query')
                    ->section('Query builder')
                    ->route('rapidez.default-query.index')
                    ->icon('<svg class="svg-icon" style="width: 2em; height: 2em;vertical-align: middle;text-align:center;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M653.328 125.024l-56.576 56.704L734.88 320H399.68C240.88 320 112 448.992 112 607.776c0 158.816 128 287.952 288 287.952v-80c-112 0-208-93.312-208-208.016 0-114.688 93.152-208 207.84-208h334.96l-137.888 137.856 56.528 56.56 234.48-234.496L653.344 125.024z" fill="#565D64" /></svg>');
            }
        );

        return $this;
    }
}
