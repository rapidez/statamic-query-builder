<?php

namespace Rapidez\StatamicQueryBuilder\Parsers;

use Exception;
use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class NotBetweenParser implements ParsesOperator
{
    public function parse(string $field, $value): array
    {
        if (! is_array($value) || count($value) !== 2) {
            throw new Exception('NOT_BETWEEN requires two values');
        }

        return ['bool' => ['must_not' => [['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]]]]];
    }
}
