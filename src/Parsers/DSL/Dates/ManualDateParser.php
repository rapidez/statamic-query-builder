<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\DateRangeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\Traits\ManualDateParserTrait;

class ManualDateEqualsParser extends DateRangeParser
{
    use ManualDateParserTrait;

    protected function buildRange($value): array
    {
        $parsedDate = $this->parseManualDate($value);
        if ($parsedDate === null) {
            return ['gte' => 'now/d', 'lte' => 'now/d'];
        }

        return ['gte' => $parsedDate, 'lte' => $parsedDate];
    }
}
