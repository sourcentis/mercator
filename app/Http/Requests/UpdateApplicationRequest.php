<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesIcon;
use App\Rules\UrlList;
use Illuminate\Validation\Rule;

class UpdateApplicationRequest extends BaseFormRequest
{
    use ValidatesIcon;

    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        foreach (['urls', 'documentation'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => UrlList::normalize($this->input($field))]);
            }
        }
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: $this->route('application')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => [
                'min:3',
                'max:64',
                'required',
                Rule::unique('applications')->where('perimeter_id', $perimeterId)
                    ->ignore($this->route('application')->id ?? $this->id)
                    ->whereNull('deleted_at'),
            ],
            'entities.*' => [
                'integer',
            ],
            'entities' => [
                'array',
            ],
            'security_need' => [
                'nullable',
                'integer',
                'min:-2147483648',
                'max:2147483647',
            ],
            'processes.*' => [
                'integer',
            ],
            'processes' => [
                'array',
            ],
            'services.*' => [
                'integer',
            ],
            'services' => [
                'array',
            ],
            'databases.*' => [
                'integer',
            ],
            'databases' => [
                'array',
            ],
            'logical_servers.*' => [
                'integer',
            ],
            'logical_servers' => [
                'array',
            ],
            'install_date' => [
                'date',
                'nullable',
            ],
            'prod_date' => [
                'date',
                'nullable',
            ],
            'update_date' => [
                'date',
                'nullable',
                // TODO : fixme
                // 'after:install_date',
            ],
            'urls' => [
                'nullable',
                new UrlList,
            ],
            'documentation' => [
                'nullable',
                new UrlList,
            ],
        ];
    }
}
