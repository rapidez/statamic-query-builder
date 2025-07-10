<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\DateRangeParser;

class YesterdayEqualsParser extends DateRangeParser
{
    protected function buildRange($value): array
    {
        return ['gte' => 'now-1d/d', 'lte' => 'now-1d/d'];
    }
}
