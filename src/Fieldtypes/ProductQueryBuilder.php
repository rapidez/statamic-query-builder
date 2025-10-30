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
        $originalData['sorting'] = $this->getSorting($data);

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

        if ($value['sorting'] = $this->getSorting($value)) {
            $value['index'] = $value['index'].'_'.$value['sorting'];
        }

        return $value;
    }

    protected function getSorting(array $data): string|bool
    {
        if (isset($data['sortField']) && $data['sortField'] != '') {
            $sortField = collect(explode('.', $data['sortField']));
            $sortDirection = $data['sortDirection'] ?? 'asc';

            return strtolower($sortField->last()).'_'.strtolower($sortDirection);
        }

        return false;
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

        if ($sorting = $this->getSorting($value)) {
            $indexName = $indexName.'_'.$sorting;
        }

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
