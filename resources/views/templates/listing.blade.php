@php
if (is_array($product_query_builder)) {
    $queryBuilder = $product_query_builder;
} else {
    $queryBuilder = $product_query_builder?->value();
}
@endphp
@if($hash = $queryBuilder['hash'])
    <x-rapidez::listing
        v-bind:base-filters="() => [window.config.productlist['{{ $hash }}']]"
        ::index="'{{ $queryBuilder['index'] }}' ?? config.index.product"
    />
@endif
