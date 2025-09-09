<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class RelativeDateBeforeParser extends RelativeDateBaseParser
{
    public function parse(string $field, mixed $value): array
    {
        $expression = $this->buildDateExpression($value);

        return [
            'range' => [
                $field => ['lt' => $expression],
            ],
        ];
    }
}
