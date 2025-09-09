<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

class ManualDateAfterParser extends ManualDateBaseParser
{
    public function parse(string $field, mixed $value): array
    {
        $parsedDate = $this->parseManualDate($value);
        if ($parsedDate === null) {
            return $this->buildFallbackQuery();
        }

        return [
            'range' => [
                $field => ['gt' => $parsedDate],
            ],
        ];
    }
}
