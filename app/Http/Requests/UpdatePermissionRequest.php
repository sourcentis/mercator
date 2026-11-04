<?php

namespace App\Http\Requests;

class UpdatePermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules()
    {
        return [
            'title' => [
                'required',
            ],
        ];
    }
}
