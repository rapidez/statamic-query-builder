<?php

namespace Rapidez\StatamicQueryBuilder\Fieldtypes;

use Illuminate\View\View;
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
            'sortField' => '',
            'sortDirection' => '',
        ];
    }

    public function process($data)
    {
        $originalData = $data;
        $originalData['value'] = $this->getDsl($data);

        $queryHash = md5(json_encode($originalData['value']));
        config(['frontend.productlist.'.$queryHash => $originalData['value']]);

        return $originalData;
    }

    public function augment($value)
    {
        if (! isset($value['value'])) {
            $value['value'] = $this->getDsl($value);
        }

        if (isset($value['value'])) {
            $value['hash'] = md5(json_encode($value['value']));
            config(['frontend.productlist.'.$value['hash'] => $value['value']]);
        }

        $model = config('rapidez.models.product');
        $value['index'] = (new $model)->searchableAs();

        return $value;
    }

    public function getDsl(array $value)
    {
        return $this->outputsDslQueryAction->build($value);
    }

    public function getTemplate(array $value)
    {
        $template = $value['builderTemplate'] ?? null;

        if (! $template) {
            return null;
        }

        $templatePath = 'rapidez-query-builder::templates.'.$template;

        if (! view()->exists($templatePath)) {
            return null;
        }

        $query = $this->getDsl($value);
        $model = config('rapidez.models.product');
        $indexName = (new $model)->searchableAs();
        $queryHash = md5(json_encode($query));
        config(['frontend.productlist.'.$queryHash => $query]);

        /** @var View $view */
        $view = view($templatePath)->with([
            'value' => $value,
            'query' => $query,
            'index' => $indexName,
            'queryHash' => $queryHash,
        ]);

        return $view->render();
    }
}
