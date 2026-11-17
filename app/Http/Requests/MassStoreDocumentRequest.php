<?php

// ============================================================
// MassStoreDocumentRequest.php
// App\Http\Requests\MassStoreDocumentRequest
// ============================================================

namespace App\Http\Requests;

class MassStoreDocumentRequest extends BaseMassFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Taille max base64 : 50 Mo encodé ≈ 68 Mo de chaîne base64
        $maxBase64 = config('mercator.max_upload_size', 51200) * 1.4;

        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.filename' => ['required', 'string', 'max:255'],
            'items.*.mimetype' => ['required', 'string', 'max:127'],
            'items.*.content' => ['required', 'string', 'max:'.(int) $maxBase64],
        ];
    }
}
