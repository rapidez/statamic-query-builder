<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class ThisMonthParser extends DateRangeParser
{
    protected function buildRange(string $value): array
    {
        return ['gte' => 'now/M', 'lt' => 'now+1M/M'];
    }
}
