<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class NotLikeParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'bool' => [
                'must_not' => [
                    [
                        'wildcard' => [
                            $field => [
                                'value' => "*{$value}*",
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
