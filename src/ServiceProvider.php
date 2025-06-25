<?php

namespace Rapidez\StatamicQueryBuilder;

use Rapidez\StatamicQueryBuilder\Actions\OutputsDslQueryAction;
use Rapidez\StatamicQueryBuilder\Fieldtypes\ProductQueryBuilder;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
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

        $this->bootConfig();
    }

    protected function bootConfig(): self
    {
        parent::bootConfig();

        $this->mergeConfigFrom(__DIR__.'/../config/query-builder.php', 'query-builder');
        $this->publishes([
            __DIR__.'/../config/query-builder.php' => config_path('rapidez/query-builder.php'),
        ], 'query-builder-config');

        return $this;
    }
}
