<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

class ThisWeekParser extends DateRangeParser
{
    protected function buildRange(string $value): array
    {
        return ['gte' => 'now/w', 'lt' => 'now+1w/w'];
    }
}
