<?php

namespace App\Http\Requests\Concerns;

use App\Services\QueryEngine\QueryDslValidator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

/**
 * Lecture et validation du DSL d'une requête enregistrée, partagées par la création,
 * la modification et les écritures en masse.
 */
trait ValidatesSavedQueryDsl
{
    /**
     * Le DSL peut être envoyé en tableau (`query`, API) ou en JSON sérialisé
     * (`query_json`, formulaire web) ; le tableau est prioritaire.
     */
    protected function mergeQueryInput(): void
    {
        if (is_array($this->input('query'))) {
            return;
        }

        $decoded = json_decode((string) $this->input('query_json', ''), true);

        $this->merge(['query' => is_array($decoded) ? $decoded : null]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->dslToValidate() as $key => $dsl) {
                // Champ déjà rejeté par les règles (absent, pas un tableau…)
                if ($validator->errors()->has($key)) {
                    continue;
                }

                if (! is_array($dsl) || empty($dsl)) {
                    $validator->errors()->add($key, 'Le DSL de la requête est invalide ou vide.');

                    continue;
                }

                try {
                    QueryDslValidator::validate($dsl);
                } catch (ValidationException $e) {
                    foreach ($e->errors() as $field => $messages) {
                        $validator->errors()->add("{$key}.{$field}", implode(' ', $messages));
                    }
                }
            }
        });
    }

    /**
     * @return array<string, mixed> clé d'erreur => DSL à valider
     */
    protected function dslToValidate(): array
    {
        return ['query' => $this->input('query')];
    }
}
