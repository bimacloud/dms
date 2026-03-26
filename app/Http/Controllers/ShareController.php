<?php

namespace App\Http\Controllers;

use App\Models\FileShare;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ShareController extends Controller
{
    /**
     * Store a new public share link.
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'password' => 'nullable|string|min:4',
            'expired_at' => 'nullable|date|after:now',
        ]);

        $document = Document::findOrFail($request->document_id);

        if (auth()->user()->role->name !== 'root' && $document->uploaded_by !== auth()->id()) {
            abort(403, 'Unauthorized to share this document.');
        }

        $share = FileShare::create([
            'document_id' => $document->id,
            'token' => Str::random(40),
            'password' => $request->password ? Hash::make($request->password) : null,
            'expired_at' => $request->expired_at,
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Share link created successfully.')->with('share_link', route('share.show', $share->token));
    }

    /**
     * View a shared document link.
     */
    public function show($token)
    {
        $share = FileShare::with('document')->where('token', $token)->firstOrFail();

        if (!$share->is_active) {
            abort(403, 'This link has been revoked.');
        }

        if ($share->expired_at && now()->greaterThan($share->expired_at)) {
            abort(403, 'This link has expired.');
        }

        // Check password protection
        if ($share->password) {
            if (!session()->has("share_{$token}_unlocked")) {
                return view('share.password', compact('share'));
            }
        }

        return view('share.show', compact('share'));
    }

    /**
     * Preview the shared document directly.
     */
    public function preview($token)
    {
        $share = FileShare::with('document')->where('token', $token)->firstOrFail();

        if (!$share->is_active || ($share->expired_at && now()->greaterThan($share->expired_at))) {
            abort(403, 'Link expired or revoked.');
        }

        if ($share->password && !session()->has("share_{$token}_unlocked")) {
            abort(403, 'Password required.');
        }

        $document = $share->document;
        $fileStorage = app(\App\Services\FileStorageService::class);

        if (!$fileStorage->exists($document->file_path)) {
            abort(404, 'File preview not found.');
        }

        return response()->file(storage_path('app/public/' . $document->file_path));
    }

    /**
     * Verify password for a shared document.
     */
    public function verifyPassword(Request $request, $token)
    {
        $request->validate(['password' => 'required']);
        $share = FileShare::where('token', $token)->firstOrFail();

        $key = 'share_password_attempts_' . $token . '_' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors(['password' => "Too many attempts. Please try again in {$seconds} seconds."]);
        }

        if (Hash::check($request->password, $share->password)) {
            RateLimiter::clear($key);
            session()->put("share_{$token}_unlocked", true);
            return redirect()->route('share.show', $token);
        }

        RateLimiter::hit($key);
        return back()->withErrors(['password' => 'Incorrect password.']);
    }

    /**
     * Delete/Revoke a share link.
     */
    public function destroy(FileShare $share)
    {
        if (auth()->user()->role->name !== 'root' && $share->created_by !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        $share->delete();
        return redirect()->back()->with('success', 'Share link revoked.');
    }
}
