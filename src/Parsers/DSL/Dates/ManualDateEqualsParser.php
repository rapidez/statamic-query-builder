<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates;

use Carbon\Carbon;

class ManualDateEqualsParser extends DateRangeParser
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

    protected function buildRange(mixed $value): array
    {
        $parsedDate = $this->parseManualDate($value);
        if ($parsedDate === null) {
            return ['gte' => 'now/d', 'lte' => 'now/d'];
        }

        return ['gte' => $parsedDate, 'lte' => $parsedDate];
    }
}
