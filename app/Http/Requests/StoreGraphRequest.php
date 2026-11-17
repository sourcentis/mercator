<?php

namespace App\Http\Requests;

use Gate;
use Symfony\Component\HttpFoundation\Response;

class StoreGraphRequest extends BaseFormRequest
{
    /**
     * Le contenu est un document XML (mxGraph / BPMN) : il ne doit pas être passé à strip_tags
     */
    protected array $rawFields = ['content'];

    public function authorize()
    {
        abort_if(Gate::denies('graph_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'min:3',
                'max:255',
                'required',
            ],
            'class' => [
                'nullable',
                'integer',
            ],
            'type' => [
                'nullable',
                'string',
                'max:255',
            ],
            'content' => [
                'nullable',
                'string',
            ],
        ];
    }
}
