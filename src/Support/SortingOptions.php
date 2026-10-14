<?php

namespace Rapidez\StatamicQueryBuilder\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Js;

class SortingOptions
{
    /**
     * @return list<array{label: string, value: string, field: string, cast?: string, index_attribute?: bool}>
     */
    public static function packageOptions(): array
    {
        return array_values(array_filter(
            config('rapidez.query-builder.sorting_options', []),
            fn ($option) => is_array($option)
                && filled($option['label'] ?? null)
                && filled($option['value'] ?? null)
                && filled($option['field'] ?? null)
        ));
    }

    /**
     * Options for the Product Query Builder CP sort dropdown.
     *
     * @return list<array{label: string, value: string}>
     */
    public static function forControlPanel(): array
    {
        $packageOptions = collect(static::packageOptions())
            ->map(fn (array $option) => [
                'label' => __('rapidez-query-builder::sorting.'.$option['label']),
                'value' => $option['value'],
            ]);

        $searchkitOptions = collect(config('rapidez.searchkit.sorting', []))
            ->filter(fn ($directions, $field) => is_string($field) && $field !== '')
            ->map(fn ($directions, $field) => [
                'label' => ucwords(str_replace('_', ' ', strtolower($field))),
                'value' => $field,
            ])
            ->values();

        return $packageOptions
            ->concat($searchkitOptions)
            ->unique('value')
            ->values()
            ->all();
    }

    /**
     * Searchkit sorting entries keyed by suffix (e.g. _popularity_desc).
     *
     * @return array<string, array{label: string, code: string, field: string, order: string, value: string, key: string}>
     */
    public static function forSearchkit(?string $productIndex = null): array
    {
        $productIndex ??= (new (config('rapidez.models.product')))->searchableAs();

        return collect(static::packageOptions())
            ->flatMap(function (array $option) use ($productIndex) {
                $code = static::sortingCode($option['value']);

                return collect(['desc', 'asc'])->mapWithKeys(function (string $direction) use ($option, $productIndex, $code) {
                    $key = "_{$code}_{$direction}";

                    return [
                        $key => [
                            'label' => __('rapidez-query-builder::sorting.'.$option['label'])
                                .($direction === 'asc' ? ' ('.__('rapidez-query-builder::sorting.ascending').')' : ''),
                            'code' => $code,
                            'field' => $option['field'],
                            'order' => $direction,
                            'value' => "{$productIndex}_{$code}_{$direction}",
                            'key' => $key,
                        ],
                    ];
                });
            })
            ->all();
    }

    /**
     * JSON for merging into a productlist/listing config-callback.
     */
    public static function forSearchkitJavaScript(?string $productIndex = null): Js
    {
        return Js::from(static::forSearchkit($productIndex));
    }

    /**
     * Magento attribute codes that need a typed ES mapping.
     *
     * @return list<string>
     */
    public static function indexAttributes(): array
    {
        return collect(static::packageOptions())
            ->filter(fn (array $option) => ($option['index_attribute'] ?? false) || isset($option['cast']))
            ->map(fn (array $option) => explode('.', $option['field'])[0])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Cast configured root-level sort fields when indexing.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function castIndexData(array $data): array
    {
        foreach (static::packageOptions() as $option) {
            $cast = $option['cast'] ?? null;
            $field = $option['field'] ?? null;

            if (! $cast || ! $field || str_contains($field, '.') || ! array_key_exists($field, $data)) {
                continue;
            }

            if ($data[$field] === null || $data[$field] === '') {
                $data[$field] = null;

                continue;
            }

            $data[$field] = match ($cast) {
                'float', 'double' => (float) $data[$field],
                'int', 'integer' => (int) $data[$field],
                default => $data[$field],
            };
        }

        return $data;
    }

    /**
     * Last path segment used by ProductQueryBuilder::getSorting().
     */
    public static function sortingCode(string $value): string
    {
        return strtolower(collect(explode('.', $value))->last());
    }

    /**
     * @return Collection<int, string>
     */
    public static function searchkitKeys(): Collection
    {
        return collect(static::packageOptions())
            ->flatMap(function (array $option) {
                $code = static::sortingCode($option['value']);

                return ["_{$code}_asc", "_{$code}_desc"];
            })
            ->values();
    }
}
