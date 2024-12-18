<?php

namespace Rapidez\StatamicQueryBuilder\Fieldtypes;

use Statamic\Fields\Fieldtype;

class QueryBuilder extends Fieldtype
{
    protected $icon = 'filter';

    protected static $handle = 'query_builder';

    protected $fields = [];

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function defaultValue()
    {
        return [
            'groups' => [
                [
                    'conjunction' => 'AND',
                    'conditions' => []
                ]
            ],
            'globalConjunction' => 'AND',
            'limit' => 100
        ];
    }

    public function preload()
    {
        return [
            'fields' => $this->fields,
            'operators' => [
                'text' => ['=', '!=', 'LIKE', 'NOT LIKE', 'STARTS_WITH', 'ENDS_WITH', 'IS_NULL', 'IS_NOT_NULL'],
                'select' => ['=', '!=', 'IN', 'NOT IN', 'IS_NULL', 'IS_NOT_NULL'],
                'number' => ['=', '!=', '>', '<', '>=', '<=', 'BETWEEN', 'NOT_BETWEEN', 'IS_NULL', 'IS_NOT_NULL'],
                'date' => ['=', '!=', '>', '<', '>=', '<=', 'BETWEEN', 'NOT_BETWEEN', 'LAST_X_DAYS', 'NEXT_X_DAYS', 'THIS_WEEK', 'THIS_MONTH']
            ],
            'defaultLimit' => 100,
            'showLimit' => true,
        ];
    }
}
