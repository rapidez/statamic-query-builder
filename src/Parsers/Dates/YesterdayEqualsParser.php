<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Parsers\Dates\DateRangeParser;

class YesterdayEqualsParser extends DateRangeParser
{
    protected function buildRange($value): array
    {
        return ['gte' => 'now-1d/d', 'lte' => 'now-1d/d'];
    }
}
