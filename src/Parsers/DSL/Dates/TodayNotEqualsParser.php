<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TodayNotEqualsParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'bool' => [
                'must_not' => [
                    'range' => [
                        $field => [
                            'gte' => 'now/d',
                            'lte' => 'now/d',
                        ],
                    ],
                ],
            ],
        ];
    }
}
