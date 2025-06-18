<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class ManualDateParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        if (!is_array($value)) {
            return ['match_all' => []];
        }

        $date = $value['date'] ?? '';
        $operator = $value['operator'] ?? '=';

        if (empty($date)) {
            return ['match_all' => []];
        }

        try {
            $parsedDate = \Carbon\Carbon::parse($date);
        } catch (\Exception $e) {
            return ['match_all' => []];
        }

        switch ($operator) {
            case '=':
                $startOfDay = $parsedDate->startOfDay()->format('Y-m-d\TH:i:s');
                $endOfDay = $parsedDate->endOfDay()->format('Y-m-d\TH:i:s');
                return [
                    'range' => [
                        $field => [
                            'gte' => $startOfDay,
                            'lte' => $endOfDay,
                        ]
                    ]
                ];
            case '>':
                return [
                    'range' => [
                        $field => ['gt' => $parsedDate->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '>=':
                return [
                    'range' => [
                        $field => ['gte' => $parsedDate->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '<':
                return [
                    'range' => [
                        $field => ['lt' => $parsedDate->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '<=':
                return [
                    'range' => [
                        $field => ['lte' => $parsedDate->format('Y-m-d\TH:i:s')]
                    ]
                ];
            case '!=':
                return [
                    'bool' => [
                        'must_not' => [
                            'range' => [
                                $field => [
                                    'gte' => $parsedDate->startOfDay()->format('Y-m-d\TH:i:s'),
                                    'lte' => $parsedDate->endOfDay()->format('Y-m-d\TH:i:s'),
                                ]
                            ]
                        ]
                    ]
                ];
            default:
                $startOfDay = $parsedDate->startOfDay()->format('Y-m-d\TH:i:s');
                $endOfDay = $parsedDate->endOfDay()->format('Y-m-d\TH:i:s');
                return [
                    'range' => [
                        $field => [
                            'gte' => $startOfDay,
                            'lte' => $endOfDay,
                        ]
                    ]
                ];
        }
    }
}