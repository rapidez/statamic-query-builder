<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class NextXDaysParser extends DateRangeParser
{
    protected function buildRange(string $value): array
    {
        return ['gte' => 'now/d', 'lte' => "now+{$value}d/d"];
    }
}
