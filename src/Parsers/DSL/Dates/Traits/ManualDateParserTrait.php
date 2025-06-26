<?php

namespace Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\Traits;

use Carbon\Carbon;

trait ManualDateParserTrait
{
    protected function parseManualDate(array $value): ?string
    {
        $date = $value['date'] ?? '';
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
