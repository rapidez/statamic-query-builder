<?php

namespace Rapidez\StatamicQueryBuilder\Fieldtypes;

use Illuminate\Support\Facades\Cache;
use Rapidez\StatamicQueryBuilder\Actions\OutputsDslQueryAction;
use Statamic\Fields\Fieldtype;

class ProductQueryBuilder extends Fieldtype
{
    protected $icon = 'filter';

    public function __construct(
        protected OutputsDslQueryAction $outputsDslQueryAction
    ) {}

    public function defaultValue()
    {
        return [
            'groups' => [
                [
                    'conjunction' => 'AND',
                    'conditions' => [],
                ],
            ],
            'globalConjunction' => 'AND',
            'limit' => 100,
            'products' => [],
        ];
    }

    public function process($data)
    {
        $data['value'] = $this->getValue($data, true);

        return $data;
    }

    public function augment($value)
    {
        unset($value['products']);

        $value['value'] = $this->getValue($value);

        return $value;
    }

    public function getValue(array $value, bool $force = false)
    {
        $cacheKey = 'product-query-builder-'.config('rapidez.store').'-'.md5(json_encode($value));

        if ($force && Cache::has($cacheKey)) {
            return Cache::forget($cacheKey);
        }

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addDay(), fn () => $this->getDsl($value));
    }

    public function getDsl(array $value)
    {
        return $this->outputsDslQueryAction->build($value);
    }
}
