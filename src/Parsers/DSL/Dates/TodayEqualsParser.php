<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class TodayEqualsParser extends DateRangeParser
{
    protected function buildRange($value): array
    {
        return ['gte' => 'now/d', 'lte' => 'now/d'];
    }
}
