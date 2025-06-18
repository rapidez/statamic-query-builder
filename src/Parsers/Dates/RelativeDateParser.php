<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class RelativeDateParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        if (!is_array($value)) {
            return ['match_all' => []];
        }

        $base = now();
        $offset = (int) ($value['offset'] ?? 1);
        $unit = $value['unit'] ?? 'days';
        $operator = $value['operator'] ?? '=';

        switch ($unit) {
            case 'days':
                $targetDate = $base->addDays($offset);
                break;
            case 'weeks':
                $targetDate = $base->addWeeks($offset);
                break;
            case 'months':
                $targetDate = $base->addMonths($offset);
                break;
            case 'years':
                $targetDate = $base->addYears($offset);
                break;
            default:
                $targetDate = $base->addDays($offset);
        }

        $dateValue = $targetDate->format('Y-m-d\TH:i:s');

        switch ($operator) {
            case '=':
                $startOfDay = $targetDate->startOfDay()->format('Y-m-d\TH:i:s');
                $endOfDay = $targetDate->endOfDay()->format('Y-m-d\TH:i:s');
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
                        $field => ['gt' => $dateValue]
                    ]
                ];
            case '>=':
                return [
                    'range' => [
                        $field => ['gte' => $dateValue]
                    ]
                ];
            case '<':
                return [
                    'range' => [
                        $field => ['lt' => $dateValue]
                    ]
                ];
            case '<=':
                return [
                    'range' => [
                        $field => ['lte' => $dateValue]
                    ]
                ];
            case '!=':
                return [
                    'bool' => [
                        'must_not' => [
                            'range' => [
                                $field => [
                                    'gte' => $targetDate->startOfDay()->format('Y-m-d\TH:i:s'),
                                    'lte' => $targetDate->endOfDay()->format('Y-m-d\TH:i:s'),
                                ]
                            ]
                        ]
                    ]
                ];
            default:
                $startOfDay = $targetDate->startOfDay()->format('Y-m-d\TH:i:s');
                $endOfDay = $targetDate->endOfDay()->format('Y-m-d\TH:i:s');
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