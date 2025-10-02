<?php

namespace Rapidez\StatamicQueryBuilder;

use Rapidez\StatamicQueryBuilder\Actions\OutputsDslQueryAction;
use Rapidez\StatamicQueryBuilder\Fieldtypes\ProductQueryBuilder;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'resources/js/statamic-query-builder.js',
    ];

    protected $fieldtypes = [
        ProductQueryBuilder::class,
    ];

    public function bootAddon(): void
    {
        $this->app->singleton(OutputsDslQueryAction::class);

        $this->bootConfig()
            ->bootModels();
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
}
