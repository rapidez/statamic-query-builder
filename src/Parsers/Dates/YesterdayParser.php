<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class YesterdayParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        $operator = is_array($value) ? ($value['operator'] ?? '=') : '=';

        $yesterday = now()->subDay()->startOfDay();
        $endOfYesterday = now()->subDay()->endOfDay();

        switch ($operator) {
            case '=':
                return [
                    'range' => [
                        $field => [
                            'gte' => $yesterday->format('Y-m-d\TH:i:s'),
                            'lte' => $endOfYesterday->format('Y-m-d\TH:i:s'),
                        ]
                    ]
                ];
            case '>':
                return [
                    'range' => [
                        $field => ['gt' => $endOfYesterday->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '>=':
                return [
                    'range' => [
                        $field => ['gte' => $yesterday->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '<':
                return [
                    'range' => [
                        $field => ['lt' => $yesterday->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '<=':
                return [
                    'range' => [
                        $field => ['lte' => $endOfYesterday->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '!=':
                return [
                    'bool' => [
                        'must_not' => [
                            'range' => [
                                $field => [
                                    'gte' => $yesterday->format('Y-m-d\TH:i:s'),
                                    'lte' => $endOfYesterday->format('Y-m-d\TH:i:s'),
                                ]
                            ]
                        ]
                    ]
                ];
            default:
                return [
                    'range' => [
                        $field => [
                            'gte' => $yesterday->format('Y-m-d\TH:i:s'),
                            'lte' => $endOfYesterday->format('Y-m-d\TH:i:s'),
                        ]
                    ]
                ];
        }
    }
}