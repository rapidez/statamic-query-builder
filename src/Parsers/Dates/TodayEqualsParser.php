<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Parsers\Dates\DateRangeParser;

class TodayEqualsParser extends DateRangeParser
{
    protected function buildRange($value): array
    {
        return ['gte' => 'now/d', 'lte' => 'now/d'];
    }
}
