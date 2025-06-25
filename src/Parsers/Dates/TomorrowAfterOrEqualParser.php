<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TomorrowAfterOrEqualParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'range' => [
                $field => ['gte' => 'now+1d/d']
            ]
        ];
    }
}
