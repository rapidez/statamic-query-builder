<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Parsers\Dates\DateRangeParser;

class RelativeDateEqualsParser extends DateRangeParser
{
    protected function buildRange($value): array
    {
        $dateExpression = $this->buildDateExpression($value);
        return ['gte' => $dateExpression, 'lte' => $dateExpression];
    }

    protected function buildDateExpression(array $value): string
    {
        $offset = $value['offset'] ?? 0;
        $unit = $value['unit'] ?? 'days';

        $unitChar = match($unit) {
            'days' => 'd',
            'weeks' => 'w',
            'months' => 'M',
            'years' => 'y',
            default => 'd'
        };

        if ($offset >= 0) {
            return "now+{$offset}{$unitChar}/d";
        } else {
            return "now{$offset}{$unitChar}/d";
        }
    }
}
