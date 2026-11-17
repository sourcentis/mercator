<?php

namespace App\Http\Requests;

use App\Models\Cartographer;
use Gate;
use Symfony\Component\HttpFoundation\Response;

class StoreCartographerRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        abort_if(Gate::denies('cartographer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $allowedTypes = implode(',', array_keys(Cartographer::cartographiableModelsList()));

        return [
            'cartographiable_type' => ['required', 'string', "in:{$allowedTypes}"],
            'cartographiable_id' => ['required', 'integer', 'min:1'],
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
