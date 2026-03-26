<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Folder;
use Illuminate\Http\Request;
use App\Services\FileStorageService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UploadController extends Controller
{
    use AuthorizesRequests;

    protected $storageService;

    public function __construct(FileStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $user = auth()->user();
        $folder = null;

        if ($request->folder_id) {
            $folder = Folder::findOrFail($request->folder_id);
            $this->authorize('update', $folder);
        }

        $file = $request->file('file');
        
        if ($user->role->name !== 'root' && !$user->hasAvailableDiskSpace($file->getSize())) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'file' => ['Insufficient disk quota space. Please contact your administrator.']
            ]);
        }
        
        // Build logical path for the user
        $logicalPath = 'drive/' . $user->id;
        if ($folder) {
            $pathNames = [];
            $pointer = $folder;
            while ($pointer) {
                array_unshift($pathNames, str($pointer->name)->slug());
                $pointer = $pointer->parent;
            }
            $logicalPath .= '/' . implode('/', $pathNames);
        } else {
            $logicalPath .= '/root';
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $this->storageService->store($file, $logicalPath, $fileName);

        // Infer Category to prevent null constraint if existing DB requires it
        // Or we just allow null if the migration made it nullable. 
        // We ensure category_id is nullable in the DB if not already. 
        $defaultCategory = \App\Models\Category::first();

        $document = Document::create([
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'type' => $file->getClientOriginalExtension() ?: 'unknown',
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'category_id' => $defaultCategory ? $defaultCategory->id : null,
            'folder_id' => $folder ? $folder->id : null,
            'uploaded_by' => $user->id,
            'tanggal_upload' => now(),
        ]);

        return response()->json([
            'success' => true,
            'document' => $document
        ]);
    }
}
