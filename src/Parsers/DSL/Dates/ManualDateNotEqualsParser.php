<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\Traits\ManualDateParserTrait;

class ManualDateNotEqualsParser implements ParsesOperator
{
    use ManualDateParserTrait;

    public function parse(string $field, mixed $value): array
    {
        $parsedDate = $this->parseManualDate($value);
        if ($parsedDate === null) {
            return $this->buildFallbackQuery();
        }

        return [
            'bool' => [
                'must_not' => [
                    'range' => [
                        $field => [
                            'gte' => $parsedDate,
                            'lte' => $parsedDate
                        ]
                    ]
                ]
            ]
        ];
    }
}
