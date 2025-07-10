<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Carbon\Carbon;
use Rapidez\StatamicQueryBuilder\Contracts\ParsesOperator;

abstract class ManualDateBaseParser implements ParsesOperator
{
    protected function parseManualDate(array $value): ?string
    {
        $date = $value['value'] ?? '';
        if (empty($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function buildFallbackQuery(): array
    {
        return ['match_all' => []];
    }
}
