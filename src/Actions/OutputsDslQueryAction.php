<?php

namespace Rapidez\StatamicQueryBuilder\Actions;

use Exception;
use Rapidez\StatamicQueryBuilder\Parsers\BetweenParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\LastXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\NextXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ThisMonthParser;
use Rapidez\StatamicQueryBuilder\Parsers\Dates\ThisWeekParser;
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

        if (! isset($this->operators[$operator])) {
            throw new Exception("Unsupported operator: {$operator}");
        }

        $parserClass = $this->operators[$operator];
        $parser = new $parserClass;

        return $parser->parse($field, $value);
    }
}
