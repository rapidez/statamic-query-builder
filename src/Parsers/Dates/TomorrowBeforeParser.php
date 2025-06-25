<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TomorrowBeforeParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'range' => [
                $field => ['lt' => 'now+1d/d']
            ]
        ];
    }
}
