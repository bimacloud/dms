<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FileController extends Controller
{
    use AuthorizesRequests;

    protected StorageService $storage;

    public function __construct(StorageService $storage)
    {
        $this->storage = $storage;
    }

    public function preview(File $file)
    {
        $this->authorize('view', $file);
        return redirect()->away($this->storage->getPreviewUrl($file));
    }

    public function thumbnail(File $file)
    {
        $this->authorize('view', $file);
        
        $url = $this->storage->getThumbnailUrl($file);
        
        if (!$url) {
            return response()->json(['error' => 'No thumbnail available'], 404);
        }
        
        return redirect()->away($url);
    }

    public function index(Request $request)
    {
        $query = File::where('user_id', auth()->id());

        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        if ($request->has('search')) {
            $query->where('display_name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('mime_type')) {
            $query->where('mime_type', $request->mime_type);
        }

        return response()->json($query->get());
    }

    public function update(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'folder_id' => 'sometimes|nullable|uuid|exists:folders,id',
        ]);

        if ($request->has('display_name')) {
            $file->display_name = $request->display_name;
        }

        if ($request->has('folder_id')) {
            if ($request->folder_id) {
                $parent = Folder::findOrFail($request->folder_id);
                $this->authorize('view', $parent);
            }
            $file->folder_id = $request->folder_id;
        }

        $file->save();

        return response()->json([
            'message' => 'File updated successfully.',
            'file' => $file
        ]);
    }

    /**
     * Request a pre-signed URL for upload.
     */
    public function getUploadUrl(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'extension' => 'required|string',
            'folder_id' => 'nullable|uuid|exists:folders,id',
        ]);

        if ($request->folder_id) {
            $folder = Folder::findOrFail($request->folder_id);
            $this->authorize('update', $folder);
        }

        $path = $this->storage->generateStoragePath(auth()->id(), $request->extension);
        $uploadUrl = $this->storage->getUploadUrl($path);

        return response()->json([
            'upload_url' => $uploadUrl,
            'storage_path' => $path,
        ]);
    }

    /**
     * Finalize upload and save metadata.
     */
    public function store(Request $request)
    {
        $request->validate([
            'display_name' => 'required|string',
            'storage_path' => 'required|string',
            'mime_type' => 'required|string',
            'size' => 'required|integer',
            'extension' => 'required|string',
            'folder_id' => 'nullable|uuid|exists:folders,id',
        ]);

        if ($request->folder_id) {
            $folder = Folder::findOrFail($request->folder_id);
            $this->authorize('update', $folder);
        }

        // Check quota
        if (!auth()->user()->hasAvailableDiskSpace($request->size)) {
            return response()->json(['message' => 'Insufficient disk space.'], 403);
        }

        $disk = $this->storage->getDisk();
        $file = File::create([
            'user_id' => auth()->id(),
            'folder_id' => $request->folder_id,
            'display_name' => $request->display_name,
            'storage_path' => $request->storage_path,
            'mime_type' => $request->mime_type,
            'size' => $request->size,
            'extension' => $request->extension,
            'disk' => $disk,
        ]);

        if (\Illuminate\Support\Str::startsWith($file->mime_type, 'image/')) {
            \App\Jobs\GenerateFileThumbnail::dispatch($file);
        }

        return response()->json([
            'message' => 'File metadata saved successfully.',
            'file' => $file
        ], 201);
    }

    /**
     * Get a signed download URL.
     */
    public function download(File $file)
    {
        $this->authorize('view', $file);

        $url = $this->storage->getDownloadUrl($file);

        return response()->json(['download_url' => $url]);
    }

    /**
     * Delete a file.
     */
    public function destroy(File $file)
    {
        $this->authorize('delete', $file);

        $this->storage->delete($file);
        $file->delete();

        return response()->json(['message' => 'File deleted successfully.']);
    }
}
