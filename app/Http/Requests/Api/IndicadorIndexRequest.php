<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndicadorIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institucion_id' => ['nullable', 'integer', 'min:1'],
            'ods_id' => ['nullable', 'integer', 'min:1'],
            'programa_derivado' => ['nullable', 'string', 'max:180'],
            'buscar' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', Rule::in(['id', 'nombre', 'fecha_actualizacion'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'include' => ['nullable', 'string', 'max:100'],
        ];
    }
}
