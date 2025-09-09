@if($hash = $product_query_builder->value()['hash'])
    <x-rapidez::listing
        item_list_id="query_builder_{{ $hash }}"
        item_list_name="query_builder_{{ $hash }}"
        v-bind:base-filters="() => [window.config.productlist['{{ $hash }}']]"
        ::index="'{{ $product_query_builder->value()['index'] }}' ?? config.index.product"
    />
@endif
