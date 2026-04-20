@php
if (is_array($product_query_builder)) {
    $queryBuilder = $product_query_builder;
} else {
    $queryBuilder = $product_query_builder;
}
@endphp
@if($hash = $queryBuilder['hash'])
    <x-rapidez::productlist
            :value="false"
            v-bind:base-filters="() => [window.config.productlist['{{ $hash }}']]"
            ::index="'{{ $queryBuilder['index'] }}' ?? config.index.product"
        >
        <x-slot:before>
            <ais-configure :hits-per-page.camel="{{ $queryBuilder['limit'] ?? 10 }}" />
        </x-slot:before>
    </x-rapidez::productlist>
@endif
