<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TodayBeforeOrEqualParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'range' => [
                $field => ['lte' => 'now/d'],
            ],
        ];
    }
}
