<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Carbon\Carbon;
use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class ManualDateNotEqualsParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        $date = $value['date'] ?? '';
        if (empty($date)) {
            return ['match_all' => []];
        }

        $parsedDate = Carbon::parse($date)->format('Y-m-d');
        return [
            'bool' => [
                'must_not' => [
                    'range' => [
                        $field => [
                            'gte' => $parsedDate,
                            'lte' => $parsedDate
                        ]
                    ]
                ]
            ]
        ];
    }
}
