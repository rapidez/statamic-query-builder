<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

abstract class RelativeDateBaseParser implements ParsesOperator
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
}
