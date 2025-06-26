@if($query ?? false)
    <x-rapidez::listing :query="json_encode($query)"></x-rapidez::listing>
@endif

