<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class YesterdayBeforeOrEqualParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'range' => [
                $field => ['lte' => 'now-1d/d']
            ]
        ];
    }
}
