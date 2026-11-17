<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // L'autorisation est gérée par Gate dans le controller
    }

    public function rules(): array
    {
        return [
            // Taille max configurable via php.ini / upload_max_filesize
            'file' => ['required', 'file', 'max:'.(config('mercator.max_upload_size', 51200))],
        ];
    }
}
