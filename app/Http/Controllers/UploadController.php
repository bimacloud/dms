<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use App\Services\StorageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UploadController extends Controller
{
    use AuthorizesRequests;

    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'folder_id' => 'nullable|uuid|exists:folders,id',
        ]);

        $user = auth()->user();
        $folder = null;

        if ($request->folder_id) {
            $folder = Folder::findOrFail($request->folder_id);
            $this->authorize('view', $folder);
        }

        // We can't check quota here accurately because we don't have the size yet
        // Unless we ask the frontend to send the size.
        
        $extension = pathinfo($request->filename, PATHINFO_EXTENSION);
        $storagePath = $this->storageService->generateStoragePath($user->id, $extension);

        // Get the disk name and register provider if needed
        $disk = $this->storageService->getDisk(); 
        
        // Find which provider was actually used
        $providerId = null;
        if (str_starts_with($disk, 's3_provider_')) {
            $providerId = (int) str_replace('s3_provider_', '', $disk);
        }

        return response()->json([
            'upload_url' => $this->storageService->getUploadUrl($storagePath),
            'storage_path' => $storagePath,
            'disk' => $disk,
            'storage_provider_id' => $providerId,
        ]);
    }

    public function complete(Request $request)
    {
        $request->validate([
            'storage_path' => 'required',
            'display_name' => 'required',
            'storage_provider_id' => 'nullable|exists:storage_providers,id',
            'folder_id' => 'nullable', // Lenient validation here, will check below
            'mime_type' => 'nullable',
            'size' => 'nullable|numeric',
        ]);

        try {
            $folderId = $request->folder_id ?: null;
            if ($folderId && !\Illuminate\Support\Str::isUuid($folderId)) {
                $folderId = null;
            }

            $file = File::create([
                'user_id' => auth()->id(),
                'folder_id' => $folderId,
                'storage_provider_id' => $request->storage_provider_id,
                'display_name' => $request->display_name,
                'storage_path' => $request->storage_path,
                'mime_type' => $request->mime_type ?? 'application/octet-stream',
                'size' => $request->size ?? 0,
                'extension' => pathinfo($request->display_name, PATHINFO_EXTENSION),
                'disk' => $request->disk ?? $this->storageService->getDisk(),
                'metadata' => ['uploaded_at' => now()->toIso8601String()],
            ]);

            // Dispatch thumbnail generation job
            if (\Illuminate\Support\Str::startsWith($file->mime_type, 'image/')) {
                \App\Jobs\GenerateFileThumbnail::dispatch($file);
            }

            return response()->json([
                'success' => true,
                'file' => $file
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Upload complete failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
