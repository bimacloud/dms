<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DriveController extends Controller
{
    use AuthorizesRequests;

    public function index($folderId = null)
    {
        $user = auth()->user();
        $currentFolder = null;
        $breadcrumbs = [];

        if ($folderId) {
            $currentFolder = Folder::findOrFail($folderId);
            $this->authorize('view', $currentFolder);

            // Build breadcrumbs
            $pointer = $currentFolder;
            while ($pointer) {
                $breadcrumbs[] = $pointer;
                $pointer = $pointer->parent;
            }
            $breadcrumbs = array_reverse($breadcrumbs);
        }

        $targetUserId = $user->id;
        if ($user->isRoot() && request()->has('user_id')) {
            $targetUserId = request('user_id');
        }

        $folders = Folder::where('user_id', $targetUserId)
            ->where('parent_id', $folderId)
            ->orderBy('name')
            ->get();

        $documents = Document::where('uploaded_by', $targetUserId)
            ->where('folder_id', $folderId)
            ->latest()
            ->get();
            
        $allFolders = Folder::where('user_id', $targetUserId)->orderBy('name')->get();

        return view('drive.index', compact('currentFolder', 'folders', 'documents', 'breadcrumbs', 'allFolders', 'targetUserId'));
    }
}
