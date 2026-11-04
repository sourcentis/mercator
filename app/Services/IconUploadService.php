<?php

namespace App\Services;

use App\Contracts\HasIconContract;
use App\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

class IconUploadService
{
    /**
     * @param  Model&HasIconContract  $model
     */
    public function handle(FormRequest $request, Model $model): void
    {
        if ($request->hasFile('iconFile')) {
            $file = $request->file('iconFile');
            $document = new Document;
            $document->filename = $file->getClientOriginalName();
            $document->mimetype = $file->getClientMimeType();
            $document->size = $file->getSize();
            $document->hash = hash_file('sha256', $file->path());
            $document->save();

            $file->move(storage_path('docs'), strval($document->id));
            $model->setIconId($document->id);
        } elseif (preg_match('/^\d+$/', $request->input('iconSelect', ''))) {
            $model->setIconId((int) $request->input('iconSelect'));
        } else {
            $model->setIconId(null);
        }
    }
}
