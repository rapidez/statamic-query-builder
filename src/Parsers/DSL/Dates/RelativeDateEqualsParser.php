<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\DateRangeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\Traits\RelativeDateExpressionTrait;

class RelativeDateEqualsParser extends DateRangeParser
{
    use RelativeDateExpressionTrait;

    protected function buildRange($value): array
    {
        $dateExpression = $this->buildDateExpression($value);
        return ['gte' => $dateExpression, 'lte' => $dateExpression];
    }
}
