<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSavedQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seul le propriétaire peut modifier
        // return $this->route('query')->user_id === auth()->id();
        return true;
    }

    public function rules(): array
    {
        return (new StoreSavedQueryRequest)->rules();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_public' => $this->boolean('is_public'),
        ]);

        $raw = $this->input('query_json', '');
        $decoded = json_decode($raw, true);

        $this->merge([
            'query' => is_array($decoded) ? $decoded : null,
        ]);
    }
}
