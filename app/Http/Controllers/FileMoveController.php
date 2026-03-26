<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FileMoveController extends Controller
{
    use AuthorizesRequests;

    public function updateDocument(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $request->validate([
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        if ($request->folder_id) {
            $folder = Folder::findOrFail($request->folder_id);
            $this->authorize('update', $folder); // must own the target folder
        }

        $document->update(['folder_id' => $request->folder_id]);

        return redirect()->back()->with('success', 'Document moved successfully.');
    }

    public function updateFolder(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $request->validate([
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        // Prevent moving folder into itself
        if ($request->folder_id == $folder->id) {
            return redirect()->back()->with('error', 'Cannot move a folder into itself.');
        }

        if ($request->folder_id) {
            $targetFolder = Folder::findOrFail($request->folder_id);
            $this->authorize('update', $targetFolder); // must own target

            // Prevent recursion: verify target is not a child of this folder
            $pointer = $targetFolder;
            while ($pointer) {
                if ($pointer->id === $folder->id) {
                    return redirect()->back()->with('error', 'Cannot move a folder into its own subfolder.');
                }
                $pointer = $pointer->parent;
            }
        }

        $folder->update(['parent_id' => $request->folder_id]);

        return redirect()->back()->with('success', 'Folder moved successfully.');
    }
}
