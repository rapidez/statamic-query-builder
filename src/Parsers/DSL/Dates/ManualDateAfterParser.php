<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Carbon\Carbon;
use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\Traits\ManualDateParserTrait;

class ManualDateAfterParser implements ParsesOperator
{
    use ManualDateParserTrait;

    public function parse(string $field, mixed $value): array
    {
        $parsedDate = $this->parseManualDate($value);
        if ($parsedDate === null) {
            return $this->buildFallbackQuery();
        }

        return [
            'range' => [
                $field => ['gt' => $parsedDate]
            ]
        ];
    }
}
