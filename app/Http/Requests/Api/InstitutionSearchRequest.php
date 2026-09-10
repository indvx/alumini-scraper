<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class InstitutionSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_query' => ['required', 'string', 'min:2', 'max:255'],
            'force_refresh' => ['nullable', 'boolean'],
        ];
    }
}
