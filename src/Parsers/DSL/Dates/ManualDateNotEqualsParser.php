<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class ManualDateNotEqualsParser extends ManualDateBaseParser
{
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
                            'lte' => $parsedDate,
                        ],
                    ],
                ],
            ],
        ];
    }
}
