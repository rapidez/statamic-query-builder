<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

class ThisYearParser extends DateRangeParser
{
    protected function buildRange(string $value): array
    {
        return ['gte' => 'now/M', 'lt' => 'now+1y/M'];
    }
}
