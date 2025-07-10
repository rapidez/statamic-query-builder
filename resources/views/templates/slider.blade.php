@if($query ?? false)
    <x-rapidez::productlist
        :value="false"
        :limit="$value['limit'] ?? null"
        :dslQuery="json_encode($query['query'])"
    />
@endif
