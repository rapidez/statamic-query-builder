<?php

namespace Rapidez\StatamicQueryBuilder\Parsers;

use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

class RangeParser implements ParsesOperator
{
    protected string $operator;

    public function parse(string $field, mixed $value): array
    {
        return [
            'bool' => [
                'should' => [
                    [
                        'range' => [
                            $field => [
                                $this->operator => $value,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
