<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL;

class StockStatusParser
{
    const FIELD_STOCK_STATUS = 'stock_status';

    const FIELD_IN_STOCK = 'in_stock';

    const VALUE_IN_STOCK = 'in_stock';

    const VALUE_OUT_OF_STOCK = 'out_of_stock';

    public function parse(string $field, mixed $value, string $operator = '='): array
    {
        $stockValue = $this->mapStockValue($value);

        if ($operator === '!=') {
            return [
                'bool' => [
                    'must_not' => [
                        'term' => [self::FIELD_IN_STOCK => $stockValue],
                    ],
                ],
            ];
        }

        return [
            'term' => [self::FIELD_IN_STOCK => $stockValue],
        ];
    }

    protected function mapStockValue(mixed $value): int
    {
        if ($value === self::VALUE_IN_STOCK) {
            return 1;
        }

        if ($value === self::VALUE_OUT_OF_STOCK) {
            return 0;
        }

        return (int) $value;
    }
}
