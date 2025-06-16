<?php

namespace Rapidez\StatamicQueryBuilder\Parsers;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class LikeParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'wildcard' => [
                $field => "*{$value}*",
            ],
        ];
    }
}
