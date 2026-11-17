<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class StoreRoleRequest extends BaseFormRequest
{
    public function authorize()
    {
        abort_if(Gate::denies('role_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'title' => [
                'required',
            ],
            'perimeter_id' => [
                'required',
                'integer',
                'exists:perimeters,id',
            ],
            'permissions.*' => [
                'integer',
            ],
            'permissions' => [
                'array',
            ],
        ];
    }
}
