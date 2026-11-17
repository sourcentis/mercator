<?php

namespace App\Http\Requests;

/**
 * Base des requêtes mass-store / mass-update de l'API.
 *
 * Chaque élément de "items" est sanitisé avec les mêmes règles que la requête
 * unitaire correspondante (MassStoreXRequest -> StoreXRequest,
 * MassUpdateXRequest -> UpdateXRequest).
 */
abstract class BaseMassFormRequest extends BaseFormRequest
{
    /**
     * Requête unitaire dont les $htmlFields s'appliquent à chaque élément.
     * Déduite du nom de la classe si null.
     */
    protected ?string $itemRequestClass = null;

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        if (! is_array($items)) {
            return;
        }

        $htmlFields = $this->itemHtmlFields();

        foreach ($items as $index => $item) {
            if (is_array($item)) {
                $items[$index] = $this->sanitize($item, $htmlFields);
            }
        }

        $this->merge(['items' => $items]);
    }

    protected function itemHtmlFields(): array
    {
        $class = $this->itemRequestClass
            ?? __NAMESPACE__.'\\'.preg_replace('/^Mass/', '', class_basename(static::class));

        if (class_exists($class) && is_subclass_of($class, BaseFormRequest::class)) {
            return (new $class)->htmlFields();
        }

        // Pas de requête unitaire sanitisante : aucun champ HTML autorisé
        return [];
    }
}
