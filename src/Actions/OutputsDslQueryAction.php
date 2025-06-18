<?php

namespace Rapidez\StatamicQueryBuilder\Actions;

use Exception;
use Rapidez\StatamicQueryBuilder\Parsers\BetweenParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\LastXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\NextXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ThisMonthParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ThisWeekParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ThisYearParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TodayParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\TomorrowParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\YesterdayParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\RelativeDateParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ManualDateParser;
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
        'TODAY' => TodayParser::class,
        'TOMORROW' => TomorrowParser::class,
        'YESTERDAY' => YesterdayParser::class,
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

    private function mapCondition(array $condition): array
    {
        $operator = strtoupper($condition['operator']);
        $field = $condition['attribute'];
        $value = $condition['value'] ?? null;

        if (is_array($value) && isset($value['type'])) {
            return $this->handleEnhancedDateValue($field, $operator, $value);
        }

        if (! isset($this->operators[$operator])) {
            throw new Exception("Unsupported operator: {$operator}");
        }

        $parserClass = $this->operators[$operator];
        $parser = new $parserClass;

        return $parser->parse($field, $value);
    }

    private function handleEnhancedDateValue(string $field, string $operator, array $value): array
    {
        if ($value['type'] === 'relative') {
            if (isset($value['value'])) {
                $relativeOperator = strtoupper($value['value']);

                if (isset($this->operators[$relativeOperator])) {
                    $parserClass = $this->operators[$relativeOperator];
                    $parser = new $parserClass;
                    return $parser->parse($field, ['operator' => $operator]);
                }
            } elseif (isset($value['base'], $value['offset'], $value['unit'])) {
                $valueWithOperator = array_merge($value, ['operator' => $operator]);
                $parser = new RelativeDateParser;
                return $parser->parse($field, $valueWithOperator);
            }
        } elseif ($value['type'] === 'manual') {
            $valueWithOperator = [
                'date' => $value['value'],
                'operator' => $operator
            ];
            $parser = new ManualDateParser;
            return $parser->parse($field, $valueWithOperator);
        }

        return ['match_all' => []];
    }
}