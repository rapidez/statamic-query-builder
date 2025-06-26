<?php

namespace Rapidez\StatamicQueryBuilder\Actions;

use Rapidez\StatamicQueryBuilder\Parsers\BetweenParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\LastXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\NextXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ThisMonthParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ThisWeekParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ThisYearParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TodayAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TodayAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TodayBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TodayBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TodayEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TodayNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TomorrowAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TomorrowAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TomorrowBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TomorrowBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TomorrowEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TomorrowNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\YesterdayAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\YesterdayAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\YesterdayBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\YesterdayBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\YesterdayEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\YesterdayNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\RelativeDateAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\RelativeDateAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\RelativeDateBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\RelativeDateBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\RelativeDateEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\RelativeDateNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ManualDateAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ManualDateAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ManualDateBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ManualDateBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ManualDateEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ManualDateNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\EndsWithParser;
use Rapidez\StatamicQueryBuilder\Parsers\GreaterThanOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\GreaterThanParser;
use Rapidez\StatamicQueryBuilder\Parsers\InParser;
use Rapidez\StatamicQueryBuilder\Parsers\IsNotNullParser;
use Rapidez\StatamicQueryBuilder\Parsers\IsNullParser;
use Rapidez\StatamicQueryBuilder\Parsers\LessThanOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\LessThanParser;
use Rapidez\StatamicQueryBuilder\Parsers\LikeParser;
use Rapidez\StatamicQueryBuilder\Parsers\NotBetweenParser;
use Rapidez\StatamicQueryBuilder\Parsers\NotInParser;
use Rapidez\StatamicQueryBuilder\Parsers\NotLikeParser;
use Rapidez\StatamicQueryBuilder\Parsers\NotTermParser;
use Rapidez\StatamicQueryBuilder\Parsers\StartsWithParser;
use Rapidez\StatamicQueryBuilder\Parsers\TermParser;

class OutputsDslQueryAction
{
    protected array $operators = [
        '=' => TermParser::class,
        '!=' => NotTermParser::class,
        'LIKE' => LikeParser::class,
        'NOT LIKE' => NotLikeParser::class,
        'STARTS_WITH' => StartsWithParser::class,
        'ENDS_WITH' => EndsWithParser::class,
        'IN' => InParser::class,
        'NOT IN' => NotInParser::class,
        '>' => GreaterThanParser::class,
        '<' => LessThanParser::class,
        '>=' => GreaterThanOrEqualParser::class,
        '<=' => LessThanOrEqualParser::class,
        'BETWEEN' => BetweenParser::class,
        'NOT_BETWEEN' => NotBetweenParser::class,
        'IS_NULL' => IsNullParser::class,
        'IS_NOT_NULL' => IsNotNullParser::class,
        'LAST_X_DAYS' => LastXDaysParser::class,
        'NEXT_X_DAYS' => NextXDaysParser::class,
        'THIS_WEEK' => ThisWeekParser::class,
        'THIS_MONTH' => ThisMonthParser::class,
        'THIS_YEAR' => ThisYearParser::class,
    ];

    protected array $dateParserMappings = [
        'relative_TODAY_=' => TodayEqualsParser::class,
        'relative_TODAY_>' => TodayAfterParser::class,
        'relative_TODAY_>=' => TodayAfterOrEqualParser::class,
        'relative_TODAY_<' => TodayBeforeParser::class,
        'relative_TODAY_<=' => TodayBeforeOrEqualParser::class,
        'relative_TODAY_!=' => TodayNotEqualsParser::class,

        'relative_TOMORROW_=' => TomorrowEqualsParser::class,
        'relative_TOMORROW_>' => TomorrowAfterParser::class,
        'relative_TOMORROW_>=' => TomorrowAfterOrEqualParser::class,
        'relative_TOMORROW_<' => TomorrowBeforeParser::class,
        'relative_TOMORROW_<=' => TomorrowBeforeOrEqualParser::class,
        'relative_TOMORROW_!=' => TomorrowNotEqualsParser::class,

        'relative_YESTERDAY_=' => YesterdayEqualsParser::class,
        'relative_YESTERDAY_>' => YesterdayAfterParser::class,
        'relative_YESTERDAY_>=' => YesterdayAfterOrEqualParser::class,
        'relative_YESTERDAY_<' => YesterdayBeforeParser::class,
        'relative_YESTERDAY_<=' => YesterdayBeforeOrEqualParser::class,
        'relative_YESTERDAY_!=' => YesterdayNotEqualsParser::class,

        'relative_dynamic_=' => RelativeDateEqualsParser::class,
        'relative_dynamic_>' => RelativeDateAfterParser::class,
        'relative_dynamic_>=' => RelativeDateAfterOrEqualParser::class,
        'relative_dynamic_<' => RelativeDateBeforeParser::class,
        'relative_dynamic_<=' => RelativeDateBeforeOrEqualParser::class,
        'relative_dynamic_!=' => RelativeDateNotEqualsParser::class,

        'manual_=' => ManualDateEqualsParser::class,
        'manual_>' => ManualDateAfterParser::class,
        'manual_>=' => ManualDateAfterOrEqualParser::class,
        'manual_<' => ManualDateBeforeParser::class,
        'manual_<=' => ManualDateBeforeOrEqualParser::class,
        'manual_!=' => ManualDateNotEqualsParser::class,
    ];

    public function build(array $config): array
    {
        $groupConjunction = strtoupper($config['globalConjunction'] ?? 'AND');
        $globalKey = $groupConjunction === 'OR' ? 'should' : 'must';
        $groups = $config['groups'] ?? [];
        $limit = (int) ($config['limit'] ?? 10);

        $clauses = [];

        foreach ($groups as $group) {
            $groupConjunction = strtoupper($group['conjunction'] ?? 'AND');
            $groupKey = $groupConjunction === 'OR' ? 'should' : 'must';
            $conditions = [];

            foreach ($group['conditions'] as $cond) {
                $conditions[] = $this->mapCondition($cond);
            }

            if (count($groups) === 1 && $groupKey === $globalKey) {
                $clauses = array_merge($clauses, $conditions);
            } else {
                $clauses[] = ['bool' => [$groupKey => $conditions]];
            }
        }

        return [
            'query' => ['bool' => [$globalKey => $clauses]],
            'size' => $limit,
            'from' => 0,
        ];
    }

    protected function mapCondition(array $condition): array
    {
        $operator = strtoupper($condition['operator']);
        $field = $condition['attribute'];
        $value = $condition['value'] ?? null;

        if (is_array($value) && isset($value['type'])) {
            $parserClass = $this->getDateParserClass($value, $operator);
            $parser = new $parserClass;
            return $parser->parse($field, $value);
        }

        if (! isset($this->operators[$operator])) {
            return ['match_all' => []];
        }

        $parserClass = $this->operators[$operator];
        $parser = new $parserClass;

        return $parser->parse($field, $value);
    }

    protected function getDateParserClass(array $value, string $operator): string
    {
        if ($value['type'] === 'relative') {
            if (isset($value['value'])) {
                $key = "relative_{$value['value']}_{$operator}";
                return $this->dateParserMappings[$key] ?? TodayEqualsParser::class;
            } else {
                $key = "relative_dynamic_{$operator}";
                return $this->dateParserMappings[$key] ?? RelativeDateEqualsParser::class;
            }
        } elseif ($value['type'] === 'manual') {
            $key = "manual_{$operator}";
            return $this->dateParserMappings[$key] ?? ManualDateEqualsParser::class;
        }

        return TodayEqualsParser::class;
    }
}
