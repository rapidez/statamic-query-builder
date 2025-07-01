<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL;

use Exception;
use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class BetweenParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        if (! is_array($value) || count($value) !== 2) {
            throw new Exception('BETWEEN requires two values');
        }

        return ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];
    }
}
