<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\StorageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = File::with(['user', 'category'])
            ->whereNotNull('category_id')
            ->latest();

        if ($user->role->name === 'admin') {
            $query->where(function ($q) use ($user) {
                // Admin sees documents from non-root users
                $q->whereHas('user.role', function ($r) {
                    $r->where('name', '!=', 'root');
                })
                // Admin sees their own documents
                ->orWhere('user_id', $user->id);
            });
        } elseif ($user->role->name !== 'root') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $query->where('display_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $files = $query->paginate(12);
        $categories = Category::all();
        $users = \App\Models\User::where('id', '!=', auth()->id())->get();

        return view('documents.index', compact('files', 'categories', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => 'nullable|array',
            'files.*' => 'file|max:102400',
            'file' => 'nullable|file|max:102400',
            'category_id' => 'required|exists:categories,id',
        ]);

        $uploadedFiles = $request->file('files') ?: ($request->file('file') ? [$request->file('file')] : []);
        
        foreach ($uploadedFiles as $uploadedFile) {
            if (auth()->user()->role->name !== 'root' && !auth()->user()->hasAvailableDiskSpace($uploadedFile->getSize())) {
                return redirect()->back()->with('error', 'Insufficient disk quota space.');
            }

            $extension = $uploadedFile->getClientOriginalExtension() ?: 'bin';
            $storagePath = $this->storageService->generateStoragePath(auth()->id(), $extension);
            
            $disk = $this->storageService->getDisk();
            Storage::disk($disk)->put($storagePath, file_get_contents($uploadedFile->getRealPath()));

            $file = File::create([
                'user_id' => auth()->id(),
                'display_name' => pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
                'storage_path' => $storagePath,
                'mime_type' => $uploadedFile->getClientMimeType(),
                'size' => $uploadedFile->getSize(),
                'extension' => $extension,
                'disk' => $disk,
                'category_id' => $request->category_id,
                'storage_provider_id' => null, // Will be resolved by default if disk is local but here it's cloud
            ]);

            // Dispatch thumbnail job
            if (\Illuminate\Support\Str::startsWith($file->mime_type, 'image/')) {
                \App\Jobs\GenerateFileThumbnail::dispatch($file);
            }
        }

        return redirect()->route('documents.index')->with('success', 'File(s) uploaded successfully.');
    }

    public function preview(File $file)
    {
        $this->authorize('view', $file);
        return redirect()->away($this->storageService->getPreviewUrl($file));
    }

    public function thumbnail(File $file)
    {
        $this->authorize('view', $file);
        
        $url = $this->storageService->getThumbnailUrl($file);
        
        if (!$url) {
            return abort(404, 'No thumbnail available');
        }
        
        return redirect()->away($url);
    }

    public function download(File $file)
    {
        $this->authorize('view', $file);
        return redirect()->away($this->storageService->getDownloadUrl($file));
    }

    public function update(Request $request, File $file)
    {
        $this->authorize('update', $file);

        $request->validate([
            'folder_id' => 'sometimes|nullable|uuid|exists:folders,id',
            'display_name' => 'sometimes|string|max:255',
        ]);

        if ($request->has('folder_id')) {
            if ($request->folder_id) {
                $folder = \App\Models\Folder::findOrFail($request->folder_id);
                $this->authorize('view', $folder);
            }
            $file->folder_id = $request->folder_id;
        }

        if ($request->has('display_name')) {
            $file->display_name = $request->display_name;
        }

        $file->save();

        $redirectUrl = $file->folder_id ? route('drive.index', $file->folder_id) : route('drive.index');
        return redirect($redirectUrl)->with('success', 'File updated.');
    }

    public function destroy(File $file)
    {
        $this->authorize('delete', $file);

        $this->storageService->delete($file);
        $file->delete();
        
        return redirect()->back()->with('success', 'File deleted successfully.');
    }
}
