<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateExpressionTrait;

class RelativeDateAfterParser implements ParsesOperator
{
    use RelativeDateExpressionTrait;

    public function parse(string $field, mixed $value): array
    {
        $expression = $this->buildDateExpression($value);
        return [
            'range' => [
                $field => ['gt' => $expression]
            ]
        ];
    }
}
