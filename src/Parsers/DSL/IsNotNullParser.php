<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class IsNotNullParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return ['exists' => ['field' => $field]];
    }
}
