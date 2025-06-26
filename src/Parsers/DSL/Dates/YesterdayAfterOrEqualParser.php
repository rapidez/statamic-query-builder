<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class YesterdayAfterOrEqualParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'range' => [
                $field => ['gte' => 'now-1d/d']
            ]
        ];
    }
}
