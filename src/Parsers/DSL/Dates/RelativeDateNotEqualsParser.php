<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

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
                            'lte' => $expression,
                        ],
                    ],
                ],
            ],
        ];
    }
}
