<?php

namespace Rapidez\StatamicQueryBuilder\Parsers;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class NotTermParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'bool' => [
                'must_not' => [
                    [
                        'term' => [
                            $field => $value,
                        ],
                    ],
                ],
            ],
        ];
    }
}
