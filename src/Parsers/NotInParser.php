<?php

namespace Rapidez\StatamicQueryBuilder\Parsers;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class NotInParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return ['bool' => ['must_not' => [['terms' => [$field => (array) $value]]]]];
    }
}
