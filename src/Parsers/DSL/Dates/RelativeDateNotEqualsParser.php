<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateBaseParser;

class RelativeDateNotEqualsParser extends RelativeDateBaseParser
{
    public function parse(string $field, mixed $value): array
    {
        $expression = $this->buildDateExpression($value);
        return [
            'bool' => [
                'must_not' => [
                    'range' => [
                        $field => [
                            'gte' => $expression,
                            'lte' => $expression
                        ]
                    ]
                ]
            ]
        ];
    }
}
