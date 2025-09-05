@if($query ?? false)
    <script>window['{{ $queryHash }}'] = @json($query)</script>
    <x-rapidez::productlist
        :value="false"
        v-bind:base-filters="() => [window['{{ $queryHash }}']]"

        {{--
        See: config.searchkit.sorting
        --}}
        index="rapidez_products_1_price_desc"
    >
        <x-slot:before>
            <ais-configure :hits-per-page.camel="{{ $value['limit'] ?? null }}" />
        </x-slot:before>
    </x-rapidez::productlist>
@endif
