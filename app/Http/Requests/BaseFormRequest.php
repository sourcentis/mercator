<?php

namespace App\Http\Requests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Autorise la modification si l'utilisateur a la permission _edit
     * OU s'il est cartographe de l'objet passé en route model binding.
     */
    protected function authorizeEdit(): bool
    {
        foreach ($this->route()->parameters() as $param) {
            if ($param instanceof Model) {
                return Gate::allows('edit-object', $param);
            }
        }

        return false;
    }

    /**
     * Champs contenant du HTML riche (CKEditor)
     * À surcharger dans les FormRequest enfants
     */
    protected array $htmlFields = [];

    /**
     * Champs jamais affichés, à ne pas modifier (mots de passe)
     */
    protected array $rawFields = ['password', 'password_confirmation'];

    public function htmlFields(): array
    {
        return $this->htmlFields;
    }

    /**
     * Sanitise des attributs qui ne passent pas par une requête HTTP
     * (ex : import Excel), avec les mêmes règles que le formulaire.
     */
    public function sanitizeAttributes(array $data): array
    {
        return $this->sanitize($data, $this->htmlFields);
    }

    protected function prepareForValidation(): void
    {
        $this->merge($this->sanitize($this->all(), $this->htmlFields));
    }

    /**
     * Sanitise les valeurs texte d'un tableau de champs :
     * HTML nettoyé pour les champs riches, balises supprimées pour les autres.
     */
    protected function sanitize(array $data, array $htmlFields): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (! is_string($value) || in_array($key, $this->rawFields, true)) {
                $sanitized[$key] = $value;

                continue;
            }

            if (in_array($key, $htmlFields, true)) {
                // Champ HTML riche : sanitiser en conservant les balises sûres
                $sanitized[$key] = clean($value); // helper de mews/purifier
            } else {
                // Champ texte : supprimer toutes les balises
                $sanitized[$key] = strip_tags($value);
            }
        }

        return $sanitized;
    }
}
