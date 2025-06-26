<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

abstract class DateRangeParser implements ParsesOperator
{
    protected string $rangeClause;

    public function parse(string $field, $value): array
    {
        return ['bool' => ['should' => [['range' => [$field => $this->buildRange($value)]]]]];
    }

    abstract protected function buildRange(string $value): array;
}
