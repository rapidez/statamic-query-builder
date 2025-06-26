<?php

namespace Rapidez\StatamicQueryBuilder\Actions;

use Exception;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\BetweenParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\LastXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\NextXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ThisMonthParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ThisWeekParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\EndsWithParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\GreaterThanOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\GreaterThanParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\InParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\IsNotNullParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\IsNullParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\LessThanOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\LessThanParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\LikeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\NotBetweenParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\NotInParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\NotLikeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\NotTermParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\StartsWithParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\TermParser;

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

            foreach ($group['conditions'] as $condition) {
                $conditions[] = $this->mapCondition($condition);
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
        $field = is_numeric($condition['attribute']) ? $condition['attribute'] : $condition['attribute'].'.keyword';
        $value = $condition['value'] ?? null;

        if (! isset($this->operators[$operator])) {
            throw new Exception("Unsupported operator: {$operator}");
        }

        $parserClass = $this->operators[$operator];
        $parser = new $parserClass;

        return $parser->parse($field, $value);
    }
}
