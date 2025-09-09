@if($hash = $product_query_builder->value()['hash'])
    <x-rapidez::listing
        v-bind:base-filters="() => [window.config.productlist['{{ $hash }}']]"
        ::index="'{{ $product_query_builder->value()['index'] }}' ?? config.index.product"
    />
@endif
