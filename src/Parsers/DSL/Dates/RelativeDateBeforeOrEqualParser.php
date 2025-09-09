<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class RelativeDateBeforeOrEqualParser extends RelativeDateBaseParser
{
    public function parse(string $field, mixed $value): array
    {
        $expression = $this->buildDateExpression($value);

        return [
            'range' => [
                $field => ['lte' => $expression],
            ],
        ];
    }
}
