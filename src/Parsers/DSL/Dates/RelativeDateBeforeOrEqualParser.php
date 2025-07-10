<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateBaseParser;

class RelativeDateBeforeOrEqualParser extends RelativeDateBaseParser
{
    public function parse(string $field, mixed $value): array
    {
        $expression = $this->buildDateExpression($value);
        return [
            'range' => [
                $field => ['lte' => $expression]
            ]
        ];
    }
}
