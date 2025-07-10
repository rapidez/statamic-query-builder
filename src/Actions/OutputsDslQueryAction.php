<?php

namespace Rapidez\StatamicQueryBuilder\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use MailerLite\LaravelElasticsearch\Facade as Elasticsearch;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\BetweenParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\LastXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\NextXDaysParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ThisMonthParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ThisWeekParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ThisYearParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TodayAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TodayAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TodayBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TodayBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TodayEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TodayNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TomorrowAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TomorrowAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TomorrowBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TomorrowBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TomorrowEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\TomorrowNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\YesterdayAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\YesterdayAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\YesterdayBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\YesterdayBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\YesterdayEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\YesterdayNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\RelativeDateNotEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ManualDateAfterParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ManualDateAfterOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ManualDateBeforeParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ManualDateBeforeOrEqualParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ManualDateEqualsParser;
use Rapidez\StatamicQueryBuilder\Parsers\DSL\Dates\ManualDateNotEqualsParser;
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
        'THIS_YEAR' => ThisYearParser::class,

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

    protected array $mappings;

    public function build(array $config): array
    {
        $groupConjunction = strtoupper($config['globalConjunction'] ?? 'AND');
        $globalKey = $groupConjunction === 'OR' ? 'should' : 'must';
        $groups = $config['groups'] ?? [];
        $limit = (int) ($config['limit'] ?? 10);

        $this->mappings = Cache::remember('rapidez-query-mappings', now()->addDay(), fn (): array => $this->getMappings());
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

    protected function mapCondition(array $condition): array
    {
        $operator = strtoupper($condition['operator']);
        $field = $this->getQueryFieldName($condition['attribute']);
        $value = $condition['value'] ?? null;

        $parser = isset($value['type'])
            ? ($value['type'] === 'relative' && isset($value['base'])
                ? "relative_dynamic_{$operator}"
                : ($value['type'] === 'manual'
                    ? "manual_{$operator}"
                    : "{$value['type']}_{$value['value']}_{$operator}"))
            : $operator;

        if (! isset($this->operators[$parser])) {
            return ['match_all' => []];
        }

        $parserClass = $this->operators[$parser];
        $parserInstance = new $parserClass;

        return $parserInstance->parse($field, $value);
    }

    private function getMappings(): array
    {
        $indexName = config('rapidez.es_prefix').'_products_'.config('rapidez.store');
        $esMappings = ElasticSearch::indices()->getMapping(['index' => $indexName]);
        $mappings = data_get($esMappings, '*.mappings.properties', []);

        return Arr::last($mappings) ?? [];
    }

    private function getQueryFieldName(string $attribute): string
    {
        if (! isset($this->mappings[$attribute])) {
            return $attribute;
        }

        $mapping = $this->mappings[$attribute];

        $keyword = $mapping['type'] === 'text' && $mapping['fields']['keyword']['type'] === 'keyword';

        return $keyword ? $attribute.'.keyword' : $attribute;
    }
}
