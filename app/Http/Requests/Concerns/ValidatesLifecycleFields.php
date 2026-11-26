<?php

namespace App\Http\Requests\Concerns;

/**
 * Validation rules for the asset lifecycle block (commissioning, warranty,
 * end of life, hardware identity), shared by physical equipments, servers,
 * workstations and databases.
 */
trait ValidatesLifecycleFields
{
    /**
     * @param  list<string>  $fields
     * @return array<string, list<string>>
     */
    protected function lifecycleRules(array $fields): array
    {
        $rules = [];
        foreach ($fields as $field) {
            $rules[$field] = match ($field) {
                'install_date', 'update_date', 'warranty_end_date', 'warranty_start_date',
                'end_of_life_date', 'purchase_date' => ['nullable', 'date'],
                'fin_value' => ['nullable', 'numeric', 'min:0'],
                default => ['nullable', 'string', 'max:255'],
            };
        }

        return $rules;
    }
}
