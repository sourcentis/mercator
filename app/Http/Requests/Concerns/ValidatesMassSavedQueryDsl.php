<?php

namespace App\Http\Requests\Concerns;

/**
 * Valide le DSL de chaque élément d'une écriture en masse de requêtes enregistrées.
 */
trait ValidatesMassSavedQueryDsl
{
    use ValidatesSavedQueryDsl;

    /**
     * @return array<string, mixed>
     */
    protected function dslToValidate(): array
    {
        $dsls = [];

        foreach ((array) $this->input('items', []) as $index => $item) {
            $dsls["items.{$index}.query"] = is_array($item) ? ($item['query'] ?? null) : null;
        }

        return $dsls;
    }
}
