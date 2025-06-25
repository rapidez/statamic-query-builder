<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\Dates;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class TomorrowAfterParser implements ParsesOperator
{
    public function parse(string $field, mixed $value): array
    {
        return [
            'range' => [
                $field => ['gt' => 'now+1d/d']
            ]
        ];
    }
}
