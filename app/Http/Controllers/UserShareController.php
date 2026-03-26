<?php

namespace App\Http\Controllers;

use App\Models\FileUserShare;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;

class UserShareController extends Controller
{
    /**
     * Display a listing of documents shared with the authenticated user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get FileUserShare records to the current user
        $shares = FileUserShare::with(['document.category', 'sharedBy'])
            ->where('shared_to', $user->id)
            ->latest()
            ->paginate(12);

        return view('documents.shared', compact('shares'));
    }

    /**
     * Share a document internally with another user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'user_id' => 'required',
            'permission' => 'required|in:view,download',
        ]);

        if ($request->user_id !== 'all') {
            $request->validate([
                'user_id' => 'exists:users,id|different:' . auth()->id(),
            ]);
        }

        $document = Document::findOrFail($request->document_id);

        if (auth()->user()->role->name !== 'root' && $document->uploaded_by !== auth()->id()) {
            abort(403, 'Unauthorized to share this document.');
        }

        if ($request->user_id === 'all') {
            $allUsers = User::where('id', '!=', auth()->id())->get();
            $count = 0;
            foreach ($allUsers as $u) {
                $existing = FileUserShare::where('document_id', $document->id)
                    ->where('shared_to', $u->id)
                    ->first();
                    
                if ($existing) {
                    $existing->update(['permission' => $request->permission]);
                } else {
                    FileUserShare::create([
                        'document_id' => $document->id,
                        'shared_by' => auth()->id(),
                        'shared_to' => $u->id,
                        'permission' => $request->permission,
                    ]);
                    $count++;
                }
            }
            return redirect()->back()->with('success', "Document shared successfully with all member accounts.");
        }

        // Check if already shared
        $existing = FileUserShare::where('document_id', $document->id)
            ->where('shared_to', $request->user_id)
            ->first();

        if ($existing) {
            $existing->update(['permission' => $request->permission]);
            return redirect()->back()->with('success', 'Share permission updated.');
        }

        FileUserShare::create([
            'document_id' => $document->id,
            'shared_by' => auth()->id(),
            'shared_to' => $request->user_id,
            'permission' => $request->permission,
        ]);

        return redirect()->back()->with('success', 'Document shared successfully.');
    }

    /**
     * Revoke an internal share.
     */
    public function destroy(FileUserShare $share)
    {
        if (auth()->user()->role->name !== 'root' && $share->shared_by !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        $share->delete();
        return redirect()->back()->with('success', 'Internal share revoked.');
    }
}
