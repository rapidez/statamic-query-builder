<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class RelativeDateAfterOrEqualParser extends RelativeDateBaseParser
{
    public function parse(string $field, mixed $value): array
    {
        $expression = $this->buildDateExpression($value);

        return [
            'range' => [
                $field => ['gte' => $expression],
            ],
        ];
    }
}
