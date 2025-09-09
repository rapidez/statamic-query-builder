<x-rapidez::productlist
        :value="false"
        v-bind:base-filters="() => [window.config.productlist['{{ $product_query_builder->value()['hash'] }}']]"
        index="{{ $product_query_builder->value()['index'] }}"
        
    >
    <x-slot:before>
        <ais-configure :hits-per-page.camel="{{ $product_query_builder->value()['limit'] ?? 10 }}" />
    </x-slot:before>
</x-rapidez::productlist>

