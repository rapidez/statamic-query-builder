<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

class LastXDaysParser extends DateRangeParser
{
    protected function buildRange(string $value): array
    {
        return ['gte' => "now-{$value}d/d", 'lte' => 'now/d'];
    }
}
