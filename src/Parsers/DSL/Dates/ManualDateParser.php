<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Carbon\Carbon;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\DateRangeParser;

class ManualDateEqualsParser extends DateRangeParser
{
    protected function buildRange($value): array
    {
        $date = $value['date'] ?? '';
        if (empty($date)) {
            return ['gte' => 'now/d', 'lte' => 'now/d'];
        }

        $parsedDate = Carbon::parse($date)->format('Y-m-d');
        return ['gte' => $parsedDate, 'lte' => $parsedDate];
    }
}
