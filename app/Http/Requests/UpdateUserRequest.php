<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('users')
                    ->ignore($this->route('user')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                Rule::unique('users')
                    ->ignore($this->route('user')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'roles.*' => [
                'integer',
            ],
            'roles' => [
                'required',
                'array',
            ],
            'granularity' => [
                'required',
                'integer',
                'min:1',
                'max:3',
            ],
        ];
    }
}
