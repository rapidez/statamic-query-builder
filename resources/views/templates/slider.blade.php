@php
$queryBuilder = $product_query_builder->value();
@endphp
@if($hash = $queryBuilder['hash'])
    <x-rapidez::productlist
            :value="false"
            v-bind:base-filters="() => [window.config.productlist['{{ $hash }}']]"
            ::index="'{{ $product_query_builder->value()['index'] }}' ?? config.index.product"
            :sorting="$queryBuilder['sorting'] ?? false"
        >
        <x-slot:before>
            <ais-configure :hits-per-page.camel="{{ $product_query_builder->value()['limit'] ?? 10 }}" />
        </x-slot:before>
    </x-rapidez::productlist>
@endif