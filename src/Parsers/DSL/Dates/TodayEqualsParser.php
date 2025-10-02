<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class TodayEqualsParser extends DateRangeParser
{
    protected function buildRange(mixed $value): array
    {
        return ['gte' => 'now/d', 'lte' => 'now/d'];
    }
}
