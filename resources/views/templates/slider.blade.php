@if($query ?? false)
    <x-rapidez-query-builder::productlist
        :value="false"
        :limit="$value['limit'] ?? null"
        :query="json_encode($query)"
    />
@endif
