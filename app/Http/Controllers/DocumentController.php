<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Category;
use App\Http\Requests\DocumentRequest;
use Illuminate\Http\Request;
use App\Services\FileStorageService;

class DocumentController extends Controller
{
    protected $storageService;

    public function __construct(FileStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Document::with(['category', 'uploader'])->latest();

        if ($user->role->name === 'admin') {
            $query->where(function ($q) use ($user) {
                // Admin sees documents from non-root users
                $q->whereHas('uploader.role', function ($r) {
                    $r->where('name', '!=', 'root');
                })
                // Admin sees their own documents
                ->orWhere('uploaded_by', $user->id)
                // Admin sees documents shared specifically with them
                ->orWhereHas('userShares', function ($s) use ($user) {
                    $s->where('shared_to', $user->id);
                });
            });
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $documents = $query->paginate(12);
        $categories = Category::all();
        $users = \App\Models\User::where('id', '!=', auth()->id())->get();

        return view('documents.index', compact('documents', 'categories', 'users'));
    }

    public function store(DocumentRequest $request)
    {
        $category = Category::findOrFail($request->category_id);
        
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            foreach ($files as $file) {
                if (auth()->user()->role->name !== 'root' && !auth()->user()->hasAvailableDiskSpace($file->getSize())) {
                    return redirect()->back()->with('error', 'Insufficient disk quota space for one or more files.');
                }

                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $this->storageService->store($file, 'documents/' . str($category->name)->slug(), $fileName);

                Document::create([
                    'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'category_id' => $category->id,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        } else {
            $file = $request->file('file');
            if (auth()->user()->role->name !== 'root' && !auth()->user()->hasAvailableDiskSpace($file->getSize())) {
                return redirect()->back()->with('error', 'Insufficient disk quota space. Please contact your administrator.');
            }

            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $this->storageService->store($file, 'documents/' . str($category->name)->slug(), $fileName);

            Document::create([
                'title' => $request->title,
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'category_id' => $category->id,
                'uploaded_by' => auth()->id(),
            ]);
        }

        return redirect()->route('documents.index')->with('success', 'Document(s) uploaded successfully.');
    }

    public function preview(Document $document)
    {
        if (!$this->storageService->exists($document->file_path)) {
            abort(404, 'File preview not found.');
        }

        return response()->file(storage_path('app/public/' . $document->file_path));
    }

    public function download(Document $document)
    {
        if (!$this->storageService->exists($document->file_path)) {
            abort(404, 'File not found for download.');
        }

        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
        $fileName = \Illuminate\Support\Str::finish($document->title, '.' . $extension);

        return $this->storageService->download($document->file_path, $fileName);
    }

    public function destroy(Document $document)
    {
        if (auth()->user()->role->name !== 'root' && $document->uploaded_by !== auth()->id()) {
            abort(403, 'Unauthorized. You can only delete your own documents.');
        }

        $this->storageService->delete($document->file_path);
        $document->delete();
        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
}
