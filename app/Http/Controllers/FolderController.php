<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FolderController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $folders = Folder::where('user_id', auth()->id())
            ->whereNull('parent_id')
            ->with(['children', 'files'])
            ->get();

        return response()->json($folders);
    }

    public function show(Folder $folder)
    {
        $this->authorize('view', $folder);

        $folder->load(['children', 'files']);

        return response()->json($folder);
    }

    public function store(Request $request)
    {
        \Log::info('FolderController@store reached. Data: ', $request->all());
        
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|uuid|exists:folders,id',
        ]);

        if ($request->parent_id) {
            $parent = Folder::findOrFail($request->parent_id);
            $this->authorize('view', $parent);
        }

        $folder = Folder::create([
            'name' => $request->name,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Folder created successfully.',
                'folder' => $folder
            ], 201);
        }

        $redirectUrl = $folder->parent_id ? route('drive.index', $folder->parent_id) : route('drive.index');
        return redirect($redirectUrl)->with('success', 'Folder created.');
    }

    public function update(Request $request, Folder $folder)
    {
        $this->authorize('update', $folder);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'sometimes|nullable|uuid|exists:folders,id',
        ]);

        if ($request->has('name')) {
            $folder->name = $request->name;
        }

        if ($request->has('parent_id')) {
            if ($request->parent_id) {
                $parent = Folder::findOrFail($request->parent_id);
                $this->authorize('view', $parent);
            }
            $folder->parent_id = $request->parent_id;
        }

        $folder->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Folder updated successfully.',
                'folder' => $folder
            ]);
        }

        $redirectUrl = $folder->parent_id ? route('drive.index', $folder->parent_id) : route('drive.index');
        return redirect($redirectUrl)->with('success', 'Folder updated.');
    }

    public function destroy(Folder $folder)
    {
        $this->authorize('delete', $folder);

        $parentId = $folder->parent_id;
        $folder->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Folder deleted successfully.']);
        }

        $redirectUrl = $parentId ? route('drive.index', $parentId) : route('drive.index');
        return redirect($redirectUrl)->with('success', 'Folder deleted.');
    }
}
