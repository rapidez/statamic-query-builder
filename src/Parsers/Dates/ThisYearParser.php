<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class ThisYearParser implements ParsesOperator
{
    public function parse(string $field, $value): array
    {
        $startOfYear = now()->startOfYear()->format('Y-m-d\TH:i:s');
        $endOfYear = now()->endOfYear()->format('Y-m-d\TH:i:s');

        return [
            'range' => [
                $field => [
                    'gte' => $startOfYear,
                    'lte' => $endOfYear,
                ]
            ]
        ];
    }
}