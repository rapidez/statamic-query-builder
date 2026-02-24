<?php

namespace Rapidez\StatamicQueryBuilder\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * @property boolean $enabled
 * @property array $query
 * @property array $groups
 * @property string $globalConjunction
 */
class DefaultQueryRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
