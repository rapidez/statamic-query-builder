<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class RelativeDateBeforeParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        $expression = $this->buildDateExpression($value);
        return [
            'range' => [
                $field => ['lt' => $expression]
            ]
        ];
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
