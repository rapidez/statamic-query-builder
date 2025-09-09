@if($product_query_builder->value()['hash'])
    <x-rapidez::listing
        item_list_id="query_builder_"
        item_list_name="query_builder_"
        v-bind:base-filters="() => [window.config.productlist['{{ $product_query_builder->value()['hash'] }}']]"
        index="{{ $product_query_builder->value()['index'] }}"
    />
@endif
