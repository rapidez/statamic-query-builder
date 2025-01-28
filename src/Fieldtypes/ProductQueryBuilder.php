<?php

namespace Rapidez\StatamicQueryBuilder\Fieldtypes;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Statamic\Fields\Fieldtype;

class ProductQueryBuilder extends Fieldtype
{
    protected $icon = 'filter';

    public function defaultValue()
    {
        return [
            'groups' => [
                [
                    'conjunction' => 'AND',
                    'conditions' => []
                ]
            ],
            'globalConjunction' => 'AND',
            'limit' => 100,
            'products' => []
        ];
    }

    public function process($data)
    {
        $data['products'] = $this->getProducts($data, true);

        return $data;
    }

    public function augment($value)
    {
        $value['products'] = $this->getProducts($value);

        return $value;
    }

    public function getProducts(array $data, bool $force = false): array
    {
        if (isset($data['products'])) {
            unset($data['products']);
        }

        $cacheKey = 'product-query-builder-' . config('rapidez.store') . '-' . md5(json_encode($data));

        if ($force && Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
        }

        $data['products'] = Cache::remember($cacheKey, Carbon::now()->addDay(), function() use ($data) {
            return $this->toSkusEav($data);
        });

        return $data['products'];
    }

    public function toSkus($data): array
    {
        $model = config('rapidez.models.product');
        $table = 'catalog_product_flat_' . config('rapidez.store');
        $query = $model::query()->select($table . '.sku');

        if (empty($data['groups'])) {
            return $query->limit($data['limit'] ?? 100)->pluck($table . '.sku')->toArray();
        }

        $globalMethod = strtolower($data['globalConjunction'] ?? 'AND') === 'and' ? 'where' : 'orWhere';

        foreach ($data['groups'] as $group) {
            if (empty($group['conditions'])) {
                continue;
            }

            $query->$globalMethod(function($query) use ($group, $table) {
                foreach ($group['conditions'] as $condition) {
                    if (empty($condition['attribute']) || empty($condition['operator'])) {
                        continue;
                    }

                    $value = $condition['value'];
                    $operator = $condition['operator'];
                    $column = $table . '.' . $condition['attribute'];
                    $method = strtolower($group['conjunction'] ?? 'AND') === 'and' ? 'where' : 'orWhere';

                    $query = $this->applyOperator($query, $method, $column, $operator, $value);
                }
            });
        }

        return $query->limit($data['limit'] ?? 100)->pluck($table . '.sku')->toArray();
    }

    public function toSkusEav($data): array
    {
        $storeId = config('rapidez.store');

        $innerQuery = DB::table('catalog_product_entity')
            ->select('catalog_product_entity.entity_id', 'catalog_product_entity.sku');

        $attributeJoins = [];
        foreach ($data['groups'] as $group) {
            if (empty($group['conditions'])) {
                continue;
            }

            foreach ($group['conditions'] as $i => $condition) {
                if (empty($condition['attribute']) || empty($condition['operator'])) {
                    continue;
                }

                $attribute = $condition['attribute'];
                if (!isset($attributeJoins[$attribute])) {
                    $backendType = DB::table('eav_attribute')
                        ->join('eav_entity_type', 'eav_entity_type.entity_type_id', '=', 'eav_attribute.entity_type_id')
                        ->where('eav_entity_type.entity_type_code', '=', 'catalog_product')
                        ->where('eav_attribute.attribute_code', '=', $attribute)
                        ->value('backend_type');

                    if ($backendType && $backendType !== 'static') {
                        $attributeJoins[$attribute] = [
                            'type' => $backendType,
                            'instances' => []
                        ];
                    }
                }

                if (isset($attributeJoins[$attribute])) {
                    $attributeJoins[$attribute]['instances'][] = $i;
                }
            }
        }

        foreach ($attributeJoins as $attribute => $info) {
            foreach ($info['instances'] as $i) {
                $alias = "eav_{$attribute}_{$i}";
                $backendType = $info['type'];
                $attributeId = DB::table('eav_attribute')
                    ->where('attribute_code', '=', $attribute)
                    ->where('entity_type_id', function($query) {
                        $query->select('entity_type_id')
                            ->from('eav_entity_type')
                            ->where('entity_type_code', '=', 'catalog_product')
                            ->limit(1);
                    })
                    ->value('attribute_id');

                if (!$attributeId) {
                    continue;
                }

                $innerQuery->selectRaw("COALESCE(
                    (SELECT value FROM catalog_product_entity_{$backendType}
                     WHERE entity_id = catalog_product_entity.entity_id
                     AND attribute_id = {$attributeId}
                     AND store_id = {$storeId}
                     LIMIT 1),
                    (SELECT value FROM catalog_product_entity_{$backendType}
                     WHERE entity_id = catalog_product_entity.entity_id
                     AND attribute_id = {$attributeId}
                     AND store_id = 0
                     LIMIT 1)
                ) as {$alias}_value");
            }
        }

        $query = DB::table(DB::raw("({$innerQuery->toSql()}) as inner_query"))
            ->select('inner_query.sku')
            ->distinct()
            ->mergeBindings($innerQuery);

        $globalMethod = strtolower($data['globalConjunction'] ?? 'AND') === 'and' ? 'where' : 'orWhere';

        foreach ($data['groups'] as $group) {
            if (empty($group['conditions'])) {
                continue;
            }

            $query->$globalMethod(function($query) use ($group, $attributeJoins) {
                foreach ($group['conditions'] as $i => $condition) {
                    if (empty($condition['attribute']) || empty($condition['operator'])) {
                        continue;
                    }

                    $value = $condition['value'];
                    $operator = $condition['operator'];
                    $attribute = $condition['attribute'];
                    $method = strtolower($group['conjunction'] ?? 'AND') === 'and' ? 'where' : 'orWhere';

                    if (isset($attributeJoins[$attribute])) {
                        $alias = "eav_{$attribute}_{$i}";
                        $column = "inner_query.{$alias}_value";

                        if ($attribute === 'price') {
                            $customerGroupId = config('rapidez.customer_group', 0);
                            $websiteId = config('rapidez.website');

                            $column = DB::raw("LEAST(inner_query.{$alias}_value,
                                COALESCE((
                                    SELECT MIN(price)
                                    FROM catalog_product_entity_tier_price
                                    WHERE entity_id = inner_query.entity_id
                                    AND customer_group_id = {$customerGroupId}
                                    AND website_id IN (0, {$websiteId})
                                ), inner_query.{$alias}_value)
                            )");
                        }
                    } else {
                        $column = "inner_query.{$attribute}";
                    }

                    $query = $this->applyOperator($query, $method, $column, $operator, $value);
                }
            });
        }

        if (!empty($data['type_filter'])) {
            $query->where('inner_query.type_id', 'NOT IN', $data['type_filter']);
        }

        return $query->limit($data['limit'] ?? 100)->pluck('sku')->toArray();
    }

    private function applyOperator($query, $method, $column, $operator, $value)
    {
        switch ($operator) {
            case 'IN':
            case 'NOT IN':
                $values = is_array($value) ? $value : explode(',', $value);
                $method = $operator === 'IN' ? $method.'In' : $method.'NotIn';
                $query->$method($column, $values);
                break;

            case 'LIKE':
            case 'NOT LIKE':
                $query->$method($column, $operator, '%' . $value . '%');
                break;

            case 'STARTS_WITH':
                $query->$method($column, 'LIKE', $value . '%');
                break;

            case 'ENDS_WITH':
                $query->$method($column, 'LIKE', '%' . $value);
                break;

            case 'IS_NULL':
                $query->whereNull($column);
                break;

            case 'IS_NOT_NULL':
                $query->whereNotNull($column);
                break;

            case 'BETWEEN':
            case 'NOT_BETWEEN':
                if (is_array($value) && isset($value[0], $value[1]) && $value[0] !== '' && $value[1] !== '') {
                    $method = $operator === 'BETWEEN' ? 'whereBetween' : 'whereNotBetween';
                    $query->$method($column, [$value[0], $value[1]]);
                }
                break;

            case 'LAST_X_DAYS':
                if (is_numeric($value)) {
                    $query->$method($column, '>=', now()->subDays($value)->startOfDay());
                }
                break;

            case 'NEXT_X_DAYS':
                if (is_numeric($value)) {
                    $query->$method($column, '<=', now()->addDays($value)->endOfDay());
                }
                break;

            case 'THIS_WEEK':
                $query->whereBetween($column, [now()->startOfWeek(), now()->endOfWeek()]);
                break;

            case 'THIS_MONTH':
                $query->whereBetween($column, [now()->startOfMonth(), now()->endOfMonth()]);
                break;

            default:
                $operator = match($operator) {
                    '=' => '=',
                    '!=' => '<>',
                    '>' => '>',
                    '>=' => '>=',
                    '<' => '<',
                    '<=' => '<=',
                    default => '='
                };
                $query->$method($column, $operator, $value);
                break;
        }

        return $query;
    }
}
