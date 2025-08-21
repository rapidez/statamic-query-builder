@if($query ?? false)
    <x-rapidez::productlist
        :value="false"
        :size="$value['limit'] ?? null"
        :dslQuery="json_encode($query['query'])"
    />
@endif
