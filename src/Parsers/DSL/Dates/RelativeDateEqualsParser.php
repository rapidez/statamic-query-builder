<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class RelativeDateEqualsParser extends DateRangeParser
{
    protected function buildDateExpression(array $value): string
    {
        $offset = $value['offset'] ?? 0;
        $unit = $value['unit'] ?? 'days';

        $unitChar = match ($unit) {
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

    protected function buildRange(mixed $value): array
    {
        $dateExpression = $this->buildDateExpression($value);

        return ['gte' => $dateExpression, 'lte' => $dateExpression];
    }
}
