<?php

// ============================================================
// UpdateDocumentRequest.php
// App\Http\Requests\UpdateDocumentRequest
// ============================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Le fichier est optionnel : on peut renommer sans ré-uploader
            'file' => ['nullable', 'file', 'max:'.(config('mercator.max_upload_size', 51200))],
            'filename' => ['nullable', 'string', 'max:255'],
        ];
    }
}
