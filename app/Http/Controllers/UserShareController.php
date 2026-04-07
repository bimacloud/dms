<?php

namespace App\Http\Controllers;

use App\Models\Share;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;

class UserShareController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of documents shared with the authenticated user.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get Share records to the current user
        $shares = Share::with(['shareable.category', 'owner'])
            ->where('shared_with_id', $user->id)
            ->where('shareable_type', File::class)
            ->has('shareable')
            ->latest()
            ->paginate(12);

        return view('documents.shared', compact('shares'));
    }

    /**
     * Share a document internally with another user.
     */
    public function store(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim($request->email))
        ]);

        $request->validate([
            'document_id' => 'required|uuid|exists:files,id',
            'email' => 'required|string',
            'permission' => 'required|in:view,download,edit',
        ]);

        if (strtolower($request->email) !== 'all') {
            $request->validate([
                'email' => 'email|exists:users,email',
            ]);

            $targetUser = User::where('email', $request->email)->first();

            if ($targetUser->id === auth()->id()) {
                return back()->withErrors(['email' => 'You cannot share a document with yourself.']);
            }
        }

        $file = File::findOrFail($request->document_id);

        $this->authorize('update', $file);

        if (strtolower($request->email) === 'all') {
            $allUsers = User::where('id', '!=', auth()->id())->get();
            foreach ($allUsers as $u) {
                Share::updateOrCreate([
                    'shareable_type' => File::class,
                    'shareable_id' => $file->id,
                    'shared_with_id' => $u->id,
                ], [
                    'owner_id' => auth()->id(),
                    'permission' => $request->permission,
                ]);
            }
            return redirect()->back()->with('success', "File shared successfully with all accounts.");
        }

        $share = Share::updateOrCreate([
            'shareable_type' => File::class,
            'shareable_id' => $file->id,
            'shared_with_id' => $targetUser->id,
        ], [
            'owner_id' => auth()->id(),
            'permission' => $request->permission,
        ]);

        if (!$share->access_token) {
            $share->access_token = Str::random(40);
            $share->save();
        }

        return redirect()->back()
            ->with('success', 'File shared successfully.')
            ->with('share_link', url("/share/{$share->access_token}"));
    }

    /**
     * Revoke an internal share.
     */
    public function destroy(Share $share)
    {
        if (auth()->id() !== $share->owner_id && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $share->delete();
        return redirect()->back()->with('success', 'Internal share revoked.');
    }
}
