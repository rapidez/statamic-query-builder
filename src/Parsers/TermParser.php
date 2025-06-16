<?php

namespace Rapidez\StatamicQueryBuilder\Parsers;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TermParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'term' => [
                $field => $value,
            ],
        ];
    }
}
