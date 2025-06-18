<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TomorrowParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        $operator = is_array($value) ? ($value['operator'] ?? '=') : '=';

        $tomorrow = now()->addDay()->startOfDay();
        $endOfTomorrow = now()->addDay()->endOfDay();

        switch ($operator) {
            case '=':
                return [
                    'range' => [
                        $field => [
                            'gte' => $tomorrow->format('Y-m-d\TH:i:s'),
                            'lte' => $endOfTomorrow->format('Y-m-d\TH:i:s'),
                        ]
                    ]
                ];
            case '>':
                return [
                    'range' => [
                        $field => ['gt' => $endOfTomorrow->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '>=':
                return [
                    'range' => [
                        $field => ['gte' => $tomorrow->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '<':
                return [
                    'range' => [
                        $field => ['lt' => $tomorrow->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '<=':
                return [
                    'range' => [
                        $field => ['lte' => $endOfTomorrow->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '!=':
                return [
                    'bool' => [
                        'must_not' => [
                            'range' => [
                                $field => [
                                    'gte' => $tomorrow->format('Y-m-d\TH:i:s'),
                                    'lte' => $endOfTomorrow->format('Y-m-d\TH:i:s'),
                                ]
                            ]
                        ]
                    ]
                ];
            default:
                return [
                    'range' => [
                        $field => [
                            'gte' => $tomorrow->format('Y-m-d\TH:i:s'),
                            'lte' => $endOfTomorrow->format('Y-m-d\TH:i:s'),
                        ]
                    ]
                ];
        }
    }
}