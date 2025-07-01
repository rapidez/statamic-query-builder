<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class EndsWithParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'wildcard' => [
                $field => [
                    'value' => "*{$value}",
                ],
            ],
        ];
    }
}
