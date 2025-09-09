<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class TomorrowEqualsParser extends DateRangeParser
{
    protected function buildRange($value): array
    {
        return ['gte' => 'now+1d/d', 'lte' => 'now+1d/d'];
    }
}
