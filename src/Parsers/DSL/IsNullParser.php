<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class IsNullParser implements ParsesOperator
{
    public function parse(string $field, $value): array
    {
        return [
            'bool' => [
                'must_not' => [
                    'exists' => [
                        'field' => $field,
                    ],
                ],
            ],
        ];
    }
}
