<?php

namespace Rapidez\StatamicQueryBuilder\Fieldtypes;

use Statamic\Fields\Fieldtype;

class QueryBuilder extends Fieldtype
{
    /** @var string */
    protected $icon = 'filter';

    /** @var string */
    protected static $handle = 'query_builder';

    protected array $fields = [];

    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function defaultValue(): array
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
            'sortField' => '',
            'sortDirection' => '',
        ];
    }

    public function preload(): array
    {
        return [
            'fields' => $this->fields,
            'operators' => [
                'text' => ['=', '!=', 'LIKE', 'NOT LIKE', 'STARTS_WITH', 'ENDS_WITH', 'IS_NULL', 'IS_NOT_NULL'],
                'select' => ['=', '!=', 'IN', 'NOT IN', 'IS_NULL', 'IS_NOT_NULL'],
                'number' => ['=', '!=', '>', '<', '>=', '<=', 'BETWEEN', 'NOT_BETWEEN', 'IS_NULL', 'IS_NOT_NULL'],
                'date' => ['=', '!=', '>', '<', '>=', '<=', 'BETWEEN', 'NOT_BETWEEN', 'LAST_X_DAYS', 'NEXT_X_DAYS', 'THIS_WEEK', 'THIS_MONTH'],
            ],
            'defaultLimit' => 100,
            'showLimit' => true,
        ];
    }
}
