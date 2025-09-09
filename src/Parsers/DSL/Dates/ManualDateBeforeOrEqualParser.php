<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class ManualDateBeforeOrEqualParser extends ManualDateBaseParser
{
    public function parse(string $field, mixed $value): array
    {
        $parsedDate = $this->parseManualDate($value);
        if ($parsedDate === null) {
            return $this->buildFallbackQuery();
        }

        return [
            'range' => [
                $field => ['lte' => $parsedDate],
            ],
        ];
    }
}
