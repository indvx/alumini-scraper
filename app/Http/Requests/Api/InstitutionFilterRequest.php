<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class InstitutionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:50'],
            'search_id' => ['nullable', 'integer', 'exists:location_searches,id'],
            'page_size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
