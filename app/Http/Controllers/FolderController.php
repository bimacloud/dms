<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FolderController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        if ($request->parent_id) {
            $parent = Folder::findOrFail($request->parent_id);
            $this->authorize('view', $parent);
        }

        Folder::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->back()->with('success', 'Folder created successfully.');
    }

    public function update(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder->update(['name' => $request->name]);

        return redirect()->back()->with('success', 'Folder renamed successfully.');
    }

    public function destroy(Folder $folder)
    {
        $this->authorize('delete', $folder);

        // Database foreign keys have cascade on delete, so this deletes subfolders and unlinks documents recursively
        $folder->delete();

        return redirect()->back()->with('success', 'Folder deleted successfully.');
    }
}
