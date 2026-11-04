<?php

namespace App\Services\QueryEngine;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validateur dédié pour le DSL du Query Engine.
 *
 * Gère la validation récursive des filtres (groupes imbriqués),
 * les opérateurs complets, et les valeurs nullables.
 */
final class QueryDslValidator
{
    protected const ALLOWED_OPERATORS = [
        '=', '!=', '<', '>', '<=', '>=',
        'like', 'not like',
        'in', 'not in',
    ];

    protected const ALLOWED_BOOLEANS = ['and', 'or', 'not'];

    protected const ALLOWED_OUTPUTS = ['graph', 'list'];

    protected array $errors = [];

    // ─────────────────────────────────────────────────────────────
    // Point d'entrée
    // ─────────────────────────────────────────────────────────────

    /**
     * Valide le DSL et retourne le tableau nettoyé.
     * Lance une ValidationException si invalide.
     */
    public static function validate(array $data): array
    {
        $instance = new self;

        return $instance->doValidate($data);
    }

    protected function doValidate(array $data): array
    {
        // ── Validation des champs de premier niveau ───────────────
        $validator = Validator::make($data, [
            'from' => ['required', 'string', 'regex:/^[a-zA-Z][a-zA-Z0-9_-]*$/'],
            'select' => ['nullable', 'array'],
            'select.*' => ['string',   'regex:/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$/'],
            'fields' => ['nullable', 'array'],
            'fields.*' => ['string',   'regex:/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$/'],
            'group_by' => ['nullable', 'array'],
            'group_by.*' => ['string',   'regex:/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$/'],
            'filters' => ['nullable', 'array'],
            'traverse' => ['nullable', 'array'],
            // traverse.* validé manuellement (format mixte : string ou {segments:[...]})
            'depth' => ['prohibited'],
            'output' => ['nullable', 'string',  'in:graph,list'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ], [
            'from.required' => 'Le champ "from" est obligatoire.',
            'from.regex' => 'Le modèle "from" ne doit contenir que des lettres, chiffres et underscores.',
            'output.in' => 'Le champ "output" doit être "graph" ou "list".',
            'depth.min' => 'La profondeur doit être entre 1 et 5.',
            'depth.max' => 'La profondeur doit être entre 1 et 5.',
            'limit.max' => 'La limite ne peut pas dépasser 1000.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        // ── Validation de GROUP BY ────────────────────────────────
        if (! empty($data['group_by'])) {
            $this->validateGroupBy($data);
        }

        // ── Validation du tableau traverse (format mixte) ─────────
        if (! empty($data['traverse'])) {
            $this->validateTraverse($data['traverse']);
        }

        // ── Validation récursive des filtres ─────────────────────
        if (! empty($data['filters'])) {
            $this->validateFilters($data['filters'], 'filters');
        }

        if (! empty($this->errors)) {
            $validator = Validator::make([], []);
            $validator->errors()->merge($this->errors);
            throw new ValidationException($validator);
        }

        $validated['filters'] = $data['filters'] ?? [];

        return $validated;
    }

    // ─────────────────────────────────────────────────────────────
    // Validation de GROUP BY
    // ─────────────────────────────────────────────────────────────

    protected function validateGroupBy(array $data): void
    {
        if (($data['output'] ?? 'list') !== 'list') {
            $this->errors['group_by'][] = 'GROUP BY n\'est disponible qu\'avec OUTPUT list.';
        }

        $fields = $data['fields'] ?? [];
        if (empty($fields)) {
            $this->errors['group_by'][] = 'GROUP BY requiert une clause FIELDS.';

            return;
        }

        foreach ($data['group_by'] as $index => $key) {
            if (is_string($key) && ! in_array($key, $fields, true)) {
                $this->errors["group_by.{$index}"][] = "Le champ de regroupement \"{$key}\" doit figurer dans FIELDS.";
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Validation du tableau traverse
    // ─────────────────────────────────────────────────────────────

    protected function validateTraverse(array $traverse): void
    {
        foreach ($traverse as $index => $item) {
            $path = "traverse.{$index}";

            if (is_string($item)) {
                // Format historique : "subnetworks.vlan"
                if (! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\.[a-zA-Z][a-zA-Z0-9_]*)*$/', $item)) {
                    $this->errors[$path][] = 'Le chemin de traversée doit être un identifiant valide (ex: subnetworks.vlan).';
                }

                continue;
            }

            if (! is_array($item)) {
                $this->errors[$path][] = 'Chaque élément de traverse doit être une chaîne ou un objet structuré.';

                continue;
            }

            // Format nouveau : { segments: [{name, hidden}, ...] }
            $this->validateTraverseObject($item, $path);
        }
    }

    protected function validateTraverseObject(array $item, string $path): void
    {
        if (! array_key_exists('segments', $item) || ! is_array($item['segments']) || empty($item['segments'])) {
            $this->errors["{$path}.segments"][] = 'Un objet traverse doit contenir une clé "segments" tableau non vide.';

            return;
        }

        foreach ($item['segments'] as $i => $segment) {
            $this->validateTraverseSegment($segment, "{$path}.segments.{$i}");
        }

        // Sémantique : dernier segment masqué = no-op
        $last = end($item['segments']);
        if (is_array($last) && ($last['hidden'] ?? false)) {
            $this->errors[$path][] = 'Le dernier segment d\'un chemin traverse ne peut pas être masqué (no-op).';
        }

        // Sémantique : tous masqués = no-op total
        $allHidden = array_reduce(
            $item['segments'],
            fn (bool $c, mixed $s) => $c && is_array($s) && ($s['hidden'] ?? false),
            true
        );
        if ($allHidden) {
            $this->errors[$path][] = 'Un chemin traverse entièrement masqué est inutile.';
        }
    }

    protected function validateTraverseSegment(mixed $segment, string $path): void
    {
        if (! is_array($segment)) {
            $this->errors[$path][] = 'Chaque segment doit être un objet {name, hidden}.';

            return;
        }

        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', (string) ($segment['name'] ?? ''))) {
            $this->errors["{$path}.name"][] = '"name" doit être un identifiant valide.';
        }

        if (! array_key_exists('hidden', $segment) || ! is_bool($segment['hidden'])) {
            $this->errors["{$path}.hidden"][] = '"hidden" doit être un booléen.';
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Validation récursive des filtres
    // ─────────────────────────────────────────────────────────────

    protected function validateFilters(array $filters, string $path): void
    {
        foreach ($filters as $i => $filter) {
            $this->validateFilter($filter, "{$path}.{$i}");
        }
    }

    protected function validateFilter(mixed $filter, string $path): void
    {
        if (! is_array($filter)) {
            $this->errors["{$path}"] = ['Le filtre doit être un objet.'];

            return;
        }

        // EXISTS / NOT EXISTS
        if (array_key_exists('exists', $filter) || array_key_exists('not_exists', $filter)) {
            $this->validateExistsFilter($filter, $path);

            return;
        }

        // Groupe
        if (array_key_exists('group', $filter)) {
            $this->validateGroup($filter, $path);

            return;
        }

        // Condition simple
        $this->validateCondition($filter, $path);
    }

    protected function validateExistsFilter(array $filter, string $path): void
    {
        $key = array_key_exists('exists', $filter) ? 'exists' : 'not_exists';
        $relation = $filter[$key] ?? null;

        if (empty($relation) || ! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $relation)) {
            $this->errors["{$path}.{$key}"] = [
                'Le nom de la relation doit être un identifiant valide.',
            ];
        }

        if (isset($filter['boolean'])
            && ! in_array(strtolower($filter['boolean']), self::ALLOWED_BOOLEANS, true)) {
            $this->errors["{$path}.boolean"] = ['Le boolean doit être "and" ou "or".'];
        }

        if (! empty($filter['conditions'])) {
            if (! is_array($filter['conditions'])) {
                $this->errors["{$path}.conditions"] = ['Les conditions doivent être un tableau.'];
            } else {
                $this->validateFilters($filter['conditions'], "{$path}.conditions");
            }
        }
    }

    protected function validateGroup(array $filter, string $path): void
    {
        // boolean est optionnel au premier niveau, obligatoire dans les groupes imbriqués
        if (isset($filter['boolean'])) {
            if (! in_array(strtolower($filter['boolean']), self::ALLOWED_BOOLEANS, true)) {
                $this->errors["{$path}.boolean"] = [
                    'Le boolean doit être "and", "or" ou "not". Obtenu : "'.$filter['boolean'].'"',
                ];
            }
        }

        if (empty($filter['group']) || ! is_array($filter['group'])) {
            $this->errors["{$path}.group"] = ['Le groupe doit contenir au moins un filtre.'];

            return;
        }

        // Récursion
        $this->validateFilters($filter['group'], "{$path}.group");
    }

    protected function validateCondition(array $filter, string $path): void
    {
        // field
        if (empty($filter['field'])) {
            $this->errors["{$path}.field"] = ['Le champ "field" est obligatoire.'];
        } elseif (! preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$/', $filter['field'])) {
            $this->errors["{$path}.field"] = [
                'Le champ "'.$filter['field'].'" contient des caractères invalides.',
            ];
        }

        // operator
        if (empty($filter['operator'])) {
            $this->errors["{$path}.operator"] = ['L\'opérateur est obligatoire.'];
        } elseif (! in_array(strtolower($filter['operator']), self::ALLOWED_OPERATORS, true)) {
            $this->errors["{$path}.operator"] = [
                'Opérateur interdit : "'.$filter['operator'].'". '
                .'Autorisés : '.implode(', ', self::ALLOWED_OPERATORS),
            ];
        }

        // value — nullable mais obligatoire (la clé doit être présente)
        if (! array_key_exists('value', $filter)) {
            $this->errors["{$path}.value"] = ['La clé "value" est obligatoire (peut être null).'];
        } else {
            $this->validateValue($filter, $path);
        }

        // boolean optionnel
        if (isset($filter['boolean'])
            && ! in_array(strtolower($filter['boolean']), self::ALLOWED_BOOLEANS, true)) {
            $this->errors["{$path}.boolean"] = [
                'Le boolean doit être "and", "or" ou "not".',
            ];
        }
    }

    protected function validateValue(array $filter, string $path): void
    {
        $op = strtolower($filter['operator'] ?? '');
        $value = $filter['value'];

        // IS NULL / IS NOT NULL → value doit être null
        if ($value === null) {
            if (! in_array($op, ['=', '!='], true)) {
                $this->errors["{$path}.value"] = [
                    'Une valeur null n\'est valide qu\'avec les opérateurs "=" et "!=".',
                ];
            }

            return;
        }

        // IN / NOT IN → valeur doit être un tableau non vide
        if (in_array($op, ['in', 'not in'], true)) {
            if (! is_array($value) || empty($value)) {
                $this->errors["{$path}.value"] = [
                    'L\'opérateur "'.$op.'" requiert un tableau de valeurs non vide.',
                ];
            }

            return;
        }

        // Opérateurs scalaires → pas de tableau
        if (is_array($value)) {
            $this->errors["{$path}.value"] = [
                'L\'opérateur "'.$op.'" ne supporte pas un tableau de valeurs.',
            ];

            return;
        }

        // LIKE → doit être une chaîne
        if (in_array($op, ['like', 'not like'], true) && ! is_string($value)) {
            $this->errors["{$path}.value"] = [
                'L\'opérateur "'.$op.'" requiert une chaîne de caractères.',
            ];
        }
    }
}
