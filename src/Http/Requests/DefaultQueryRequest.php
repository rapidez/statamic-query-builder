<?php

namespace Rapidez\StatamicQueryBuilder\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property bool $enabled
 * @property array $query
 * @property array $groups
 * @property string $globalConjunction
 */
class DefaultQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage default query') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'query' => ['required', 'array'],
            'query.groups' => ['nullable', 'array'],
            'query.globalConjunction' => ['nullable', 'string'],
        ];
    }
}
