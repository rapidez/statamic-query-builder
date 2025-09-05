@if($query ?? false)
    <script>window['{{ $queryHash }}'] = @json($query)</script>
    {{-- A limit doesn't make sense here as there is a ais-sort-by component --}}
    <x-rapidez::listing
        v-bind:base-filters="() => [window['{{ $queryHash }}']]"
        {{-- But a default sorting could be set: --}}
        index="rapidez_products_1_price_desc"
    />
@endif
