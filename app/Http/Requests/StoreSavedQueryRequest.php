<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesSavedQueryDsl;
use Illuminate\Foundation\Http\FormRequest;

class StoreSavedQueryRequest extends FormRequest
{
    use ValidatesSavedQueryDsl;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
            // 'present' accepte un array même vide, contrairement à 'required'
            // La présence de 'from' est vérifiée dans withValidator via QueryDslValidator
            'query' => 'present|array',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_public' => $this->boolean('is_public'),
            'user_id' => auth()->id(),
        ]);

        $this->mergeQueryInput();
    }
}
