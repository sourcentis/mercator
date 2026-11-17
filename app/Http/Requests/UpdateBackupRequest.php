<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateBackupRequest extends BaseFormRequest
{
    protected array $htmlFields = ['description'];

    public function authorize(): bool
    {
        return $this->authorizeEdit();
    }

    public function rules(): array
    {
        $backupId = $this->route('backup')?->id;

        $perimeterId = $this->input('perimeter_id') ?: $this->route('backup')?->perimeter_id;

        return [
            'perimeter_id' => ['nullable', 'integer', Rule::in(auth()->user()?->perimeterIds() ?? [])],
            'name' => ['required', 'string', 'max:255', Rule::unique('backups', 'name')->where('perimeter_id', $perimeterId)->ignore($backupId)->whereNull('deleted_at')],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'backup_frequency' => ['nullable', 'integer', 'min:1', 'max:4'],
            'backup_cycle' => ['nullable', 'integer', 'min:1', 'max:6'],
            'backup_retention' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
