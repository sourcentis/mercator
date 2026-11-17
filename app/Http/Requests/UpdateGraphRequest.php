<?php

namespace App\Http\Requests;

class UpdateGraphRequest extends BaseFormRequest
{
    /**
     * Le contenu est un document XML (mxGraph / BPMN) : il ne doit pas être passé à strip_tags
     */
    protected array $rawFields = ['content'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
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
