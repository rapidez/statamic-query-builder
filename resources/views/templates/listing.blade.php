@if($query ?? false)
    <x-rapidez::listing :query="json_encode($query)" :limit="$value['limit'] ?? null"></x-rapidez::listing>
@endif

