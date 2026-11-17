<?php

namespace App\Http\Requests\Concerns;

use App\Contracts\HasIconContract;
use App\Models\Document;
use Closure;

/**
 * Validation of the custom icon fields handled by IconUploadService.
 *
 * The rules are merged into the request's own rules, so a request using this trait
 * cannot forget them, and validation fails before the object is created or updated.
 */
trait ValidatesIcon
{
    protected function validationRules()
    {
        return array_merge(parent::validationRules(), $this->iconRules());
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function iconRules(): array
    {
        return [
            // Size in kilobytes: 51200 KB = 50 MB
            'iconFile' => ['nullable', 'file', 'mimes:png', 'max:'.config('mercator.max_upload_size', 51200)],
            'iconSelect' => ['nullable', function (string $attribute, mixed $value, Closure $fail) {
                if (! is_string($value) || ! preg_match('/^\d+$/', $value)) {
                    return; // not an id: IconUploadService resets the icon
                }

                // Keeping the current icon of the edited object is always allowed
                if ((int) $value === $this->currentIconId()) {
                    return;
                }

                // Otherwise only a document already used as an icon may be picked: picking any
                // document id would make a private image public through Document::isIcon().
                $document = Document::query()->find((int) $value);
                if ($document === null || ! $document->isIcon()) {
                    $fail(trans('validation.exists', ['attribute' => $attribute]));
                }
            }],
        ];
    }

    private function currentIconId(): ?int
    {
        foreach ($this->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof HasIconContract) {
                return $parameter->getIconId();
            }
        }

        return null;
    }
}
