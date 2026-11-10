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
        // Absent = inchangé : une modification ne doit pas rendre la requête privée par défaut.
        if ($this->has('is_public')) {
            $this->merge(['is_public' => $this->boolean('is_public')]);
        }

        $this->mergeQueryInput();
    }
}
