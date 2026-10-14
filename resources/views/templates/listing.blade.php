@php
if (is_array($product_query_builder)) {
    $queryBuilder = $product_query_builder;
} else {
    $queryBuilder = $product_query_builder?->value();
}

$queryBuilderSorting = \Rapidez\StatamicQueryBuilder\Support\SortingOptions::forSearchkitJavaScript();
@endphp
@if($hash = $queryBuilder['hash'])
    <x-rapidez::listing
        snapshot="query-builder-{{ md5($hash . ($queryBuilder['index'] ?? '')) }}"
        v-bind:base-filters="() => [window.config.productlist['{{ $hash }}']]"
        ::index="'{{ $queryBuilder['index'] }}' ?? config.index.product"
        v-bind:config-callback="(searchkitConfig) => ({
            ...searchkitConfig,
            sorting: {
                ...(searchkitConfig.sorting || {}),
                ...{{ $queryBuilderSorting }}
            }
        })"
    />
@endif
