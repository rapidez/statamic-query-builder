<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TodayParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        $operator = is_array($value) ? ($value['operator'] ?? '=') : '=';

        $today = now()->startOfDay();
        $endOfToday = now()->endOfDay();

        switch ($operator) {
            case '=':
                return [
                    'range' => [
                        $field => [
                            'gte' => $today->format('Y-m-d\TH:i:s'),
                            'lte' => $endOfToday->format('Y-m-d\TH:i:s'),
                        ]
                    ]
                ];
            case '>':
                return [
                    'range' => [
                        $field => ['gt' => $endOfToday->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '>=':
                return [
                    'range' => [
                        $field => ['gte' => $today->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '<':
                return [
                    'range' => [
                        $field => ['lt' => $today->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '<=':
                return [
                    'range' => [
                        $field => ['lte' => $endOfToday->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '!=':
                return [
                    'bool' => [
                        'must_not' => [
                            'range' => [
                                $field => [
                                    'gte' => $today->format('Y-m-d\TH:i:s'),
                                    'lte' => $endOfToday->format('Y-m-d\TH:i:s'),
                                ]
                            ]
                        ]
                    ]
                ];
            default:
                return [
                    'range' => [
                        $field => [
                            'gte' => $today->format('Y-m-d\TH:i:s'),
                            'lte' => $endOfToday->format('Y-m-d\TH:i:s'),
                        ]
                    ]
                ];
        }
    }
}