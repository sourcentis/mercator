<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    public function get(int $id)
    {
        // Find the document
        $document = Document::Find($id);
        abort_if($document === null, Response::HTTP_NOT_FOUND, '404 Not Found');

        // Check the user may see this document
        abort_if(! $this->canAccess($document), Response::HTTP_FORBIDDEN, '403 Forbidden');

        // Get the path to the file
        $path = storage_path('docs/'.$id);

        // Check file exists
        abort_if(! file_exists($path), Response::HTTP_NOT_FOUND, '404 Not Found');

        // Get the content of the file
        $file_contents = file_get_contents($path);

        // Return the file
        return response($file_contents)
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', $document->mimetype)
            ->header('Content-length', strval(strlen($file_contents)))
            ->header('Content-Disposition', 'attachment; filename="'.$document->filename.'"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    public function store(Request $request)
    {
        // Require an actual file and cap its size. Without this any
        // authenticated user (including read-only roles) could write
        // unbounded files to storage/docs, exhausting disk space.
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $file = $request->file('file');

        if (! $file) {
            Log::error('DocumentController.store : File not received');

            return response()->json(['error' => 'Invalid file'], Response::HTTP_BAD_REQUEST);
        }

        Log::debug('DocumentController.store : Upload info', [
            'isValid' => $file->isValid(),
            'originalName' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'path' => $file->path(),
            'isFile' => is_file($file->path()),
        ]);

        if (! $file->isValid()) {
            Log::error('DocumentController.store : Invalid file');

            return response()->json(['error' => 'Invalid file'], Response::HTTP_BAD_REQUEST);
        }

        // Create a new document
        $document = new Document;
        $document->filename = $file->getClientOriginalName();
        $document->mimetype = $file->getClientMimeType();
        $document->size = $file->getSize();
        $document->hash = hash_file('sha256', $file->path());

        // Save the document
        $document->save();

        // Move the file to storage
        $file->move(storage_path('docs'), strval($document->id));

        // Attach the document to the session
        $documents = session()->get('documents', []);
        $documents[] = $document->id;
        session()->put('documents', $documents);

        // Return success
        return response()->json(
            ['success' => $document->filename,
                'id' => $document->id,
            ]
        );
    }

    public function delete(int $id)
    {
        // Only documents of the object being created/edited (put in session by its controller)
        abort_if(
            ! in_array($id, session('documents', [])) && Gate::denies('configure'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );

        // Find the document
        $document = Document::Find($id);
        if ($document === null) {
            // TODO: changement me
            return redirect('image/list')->with('errorMessage', 'File not found !');
        }

        // Get path to the document
        $path = storage_path('docs/'.$document->id);

        // Remove the file
        if (file_exists($path)) {
            unlink($path);
        }

        // Remove from session
        $documents = session()->get('documents');
        if ($documents !== null) {
            $key = array_search($id, $documents);
            if ($key !== false) {
                unset($documents[$key]);
            }
            session()->put('documents', $documents);
        }

        // Remove the object
        $document->delete();

        return null;
    }

    public function stats()
    {
        abort_if(Gate::denies('configure'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $count = Document::count();
        $sum = Document::sum('size');

        return view('admin.config.documents')
            ->with('count', $count)
            ->with('sum', $sum);
    }

    public function check()
    {
        abort_if(Gate::denies('configure'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $documents = Document::All();

        return view('admin.config.check')
            ->with('documents', $documents);
    }

    /**
     * A document is accessible when it has just been uploaded or is being edited
     * in this session, when it is a custom icon (shown on the maps), or when the user
     * may see one of the objects it is attached to.
     */
    private function canAccess(Document $document): bool
    {
        return in_array($document->id, session('documents', []))
            || Gate::allows('configure')
            || $document->isIcon()
            || $document->isVisibleBy(auth()->user());
    }
}
