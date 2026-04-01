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
        $request->validate([
            'shareable_type' => 'required|string|in:file,folder',
            'shareable_id' => 'required|uuid',
            'shared_with_id' => 'nullable|exists:users,id',
            'permission' => 'nullable|string|in:view,edit',
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

        return response()->json([
            'message' => 'Share created successfully.',
            'share' => $share,
            'link' => $share->access_token ? url("/api/shares/{$share->access_token}") : null
        ], 201);
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
            return response()->json(['message' => 'Share has expired.'], 403);
        }

        // Check private share
        if ($share->shared_with_id && (auth()->guest() || auth()->id() !== $share->shared_with_id)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Check password
        if ($share->password) {
            if (!$request->has('password') || !Hash::check($request->password, $share->password)) {
                return response()->json(['message' => 'Password required or incorrect.'], 401);
            }
        }

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
