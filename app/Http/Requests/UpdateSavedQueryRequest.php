<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesSavedQueryDsl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSavedQueryRequest extends FormRequest
{
    use ValidatesSavedQueryDsl;

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

        $this->mergeQueryInput();
    }
}
