<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class StoreBackupRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        abort_if(Gate::denies('backup_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    public function rules(): array
    {
        $perimeterId = $this->input('perimeter_id') ?: auth()->user()?->activeOrDefaultPerimeterId();

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => ['required', 'string', 'max:255', Rule::unique('backups', 'name')->where('perimeter_id', $perimeterId)->whereNull('deleted_at')],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'backup_frequency' => ['nullable', 'integer', 'min:1', 'max:4'],
            'backup_cycle' => ['nullable', 'integer', 'min:1', 'max:6'],
            'backup_retention' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
