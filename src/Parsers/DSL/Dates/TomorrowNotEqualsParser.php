<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TomorrowNotEqualsParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'bool' => [
                'must_not' => [
                    'range' => [
                        $field => [
                            'gte' => 'now+1d/d',
                            'lte' => 'now+1d/d'
                        ]
                    ]
                ]
            ]
        ];
    }
}
