<?php

namespace Rapidez\StatamicQueryBuilder;

use Illuminate\Support\ServiceProvider;

class StatamicQueryBuilderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rapidez/statamic-query-builder.php', 'rapidez.statamic-query-builder');
    }

    public function boot()
    {
        $this
            ->bootRoutes()
            ->bootViews()
            ->bootPublishables()
            ->bootFilters();
    }

    public function bootRoutes() : self
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        return $this;
    }

    public function bootViews() : self
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rapidez-statamic-query-builder');

        return $this;
    }

    public function bootPublishables() : self
    {
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rapidez-statamic-query-builder'),
        ], 'rapidez-statamic-query-builder-views');

        $this->publishes([
            __DIR__.'/../config/rapidez/statamic-query-builder.php' => config_path('rapidez/statamic-query-builder.php'),
        ], 'rapidez-statamic-query-builder-config');

        return $this;
    }

    public function bootFilters() : self
    {
        Eventy::addFilter('index.product.data', function ($data) {
            // Manipulate the data
            return $data;
        });

        Eventy::addFilter('index.product.mapping', fn ($mapping) => array_merge_recursive($mapping ?: [], [
            'properties' => [
                // Additional mappings
            ],
        ]));
    }
}
