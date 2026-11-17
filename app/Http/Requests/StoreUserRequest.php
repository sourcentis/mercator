<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreUserRequest extends BaseFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
            'email' => [
                'required',
                Rule::unique('users')->whereNull('deleted_at'),
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
                'nullable',
                'integer',
                'min:1',
                'max:3',
            ],
        ];
    }
}
