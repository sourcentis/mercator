<?php

namespace App\Http\Requests;

class UpdateCartographerRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (empty($this->user_id) && empty($this->role_id)) {
                $v->errors()->add('user_id', 'Un utilisateur ou un rôle est requis.');
            }
        });
    }
}
