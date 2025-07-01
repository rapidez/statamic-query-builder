<?php

namespace Rapidez\StatamicQueryBuilder\Contracts;

interface ParsesOperator
{
    public function parse(string $field, mixed $value): array;
}
