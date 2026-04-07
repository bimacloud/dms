<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\Share;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShareController extends Controller
{
    use AuthorizesRequests;

    protected StorageService $storage;

    public function __construct(StorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Store a new share (Public or Private).
     */
    public function store(Request $request)
    {
        // Handle field name mismatch from frontend (document_id vs shareable_id)
        if (!$request->has('shareable_id') && $request->has('document_id')) {
            $request->merge(['shareable_id' => $request->document_id]);
        }

        // Default shareable_type to 'file' if not provided (backward compatibility)
        if (!$request->has('shareable_type')) {
            $request->merge(['shareable_type' => 'file']);
        }

        $request->validate([
            'shareable_type' => 'required|string|in:file,folder',
            'shareable_id' => 'required|uuid',
            'shared_with_id' => 'nullable|exists:users,id',
            'permission' => 'nullable|string|in:view,download,edit',
            'password' => 'nullable|string|min:4',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $modelClass = $request->shareable_type === 'file' ? File::class : Folder::class;
        $shareable = $modelClass::findOrFail($request->shareable_id);

        $this->authorize('update', $shareable);

        $share = Share::create([
            'shareable_type' => $modelClass,
            'shareable_id' => $shareable->id,
            'owner_id' => auth()->id(),
            'shared_with_id' => $request->shared_with_id,
            'permission' => $request->permission ?? 'view',
            'access_token' => $request->shared_with_id ? null : Str::random(40),
            'password' => $request->password ? Hash::make($request->password) : null,
            'expires_at' => $request->expires_at,
        ]);

        $link = $share->access_token ? url("/share/{$share->access_token}") : null;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Share created successfully.',
                'share' => $share,
                'link' => $link
            ], 201);
        }

        return redirect()->back()
            ->with('success', 'Share created successfully.')
            ->with('share_link', $link);
    }

    /**
     * Access a shared item.
     */
    public function show(Request $request, $tokenOrId)
    {
        $share = Share::where('access_token', $tokenOrId)
            ->orWhere('id', $tokenOrId)
            ->firstOrFail();

        // Check expiry
        if ($share->expires_at && $share->expires_at->isPast()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Share has expired.'], 403);
            }
            abort(403, 'This share link has expired.');
        }

        // Check private share
        if ($share->shared_with_id && (auth()->guest() || auth()->id() !== $share->shared_with_id)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        // Check password
        if ($share->password) {
            // Check session for unlocked shares
            $sessionKey = "share_unlocked_{$share->id}";
            $isUnlocked = session()->get($sessionKey) === true;
            
            // Also allow direct password param (for API/AJAX)
            $passwordProvided = $request->has('password') && Hash::check($request->password, $share->password);

            if (!$isUnlocked && !$passwordProvided) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Password required or incorrect.'], 401);
                }
                return view('share.password', compact('share'));
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            $shareable = $share->shareable;
            if ($share->shareable_type === File::class) {
                return response()->json([
                    'share' => $share,
                    'file' => $shareable,
                    'download_url' => $this->storage->getDownloadUrl($shareable)
                ]);
            }

            return response()->json([
                'share' => $share,
                'folder' => $shareable->load(['children', 'files'])
            ]);
        }

        return view('share.show', compact('share'));
    }

    /**
     * Verify share password and store in session.
     */
    public function verifyPassword(Request $request, $token)
    {
        $share = Share::where('access_token', $token)->firstOrFail();
        
        $request->validate([
            'password' => 'required|string',
        ]);

        if (Hash::check($request->password, $share->password)) {
            session()->put("share_unlocked_{$share->id}", true);
            return redirect()->route('share.show', $token);
        }

        return redirect()->back()->withErrors(['password' => 'Incorrect password.']);
    }

    /**
     * Preview shared file content.
     */
    public function preview($token)
    {
        $share = Share::where('access_token', $token)->firstOrFail();
        
        // Ensure access is still valid
        if ($share->password && !session()->has("share_unlocked_{$share->id}")) {
            abort(401, 'Password required.');
        }

        $shareable = $share->shareable;
        
        if ($share->shareable_type === File::class) {
            return redirect()->route('documents.preview', $shareable->id);
        }

        abort(404, 'Preview only available for files.');
    }

    /**
     * Revoke a share.
     */
    public function destroy(Share $share)
    {
        if (auth()->id() !== $share->owner_id && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $share->delete();

        return response()->json(['message' => 'Share revoked successfully.']);
    }
}
