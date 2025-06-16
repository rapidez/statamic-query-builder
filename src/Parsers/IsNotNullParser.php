<?php

namespace Rapidez\StatamicQueryBuilder\Parsers;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class IsNotNullParser implements ParsesOperator
{
    public function parse(string $field, $value): array
    {
        return ['exists' => ['field' => $field]];
    }
}
