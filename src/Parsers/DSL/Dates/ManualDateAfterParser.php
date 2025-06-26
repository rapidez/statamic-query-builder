<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Carbon\Carbon;
use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class ManualDateAfterParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        $date = $value['date'] ?? '';
        if (empty($date)) {
            return ['match_all' => []];
        }

        $parsedDate = Carbon::parse($date)->format('Y-m-d');
        return [
            'range' => [
                $field => ['gt' => $parsedDate]
            ]
        ];
    }
}
