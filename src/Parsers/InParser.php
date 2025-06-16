<?php

namespace Rapidez\StatamicQueryBuilder\Parsers;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class InParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return ['terms' => [$field => (array) $value]];
    }
}
