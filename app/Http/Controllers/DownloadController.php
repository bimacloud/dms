<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Share;
use App\Models\DownloadToken;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    protected $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Generate a temporary download token for a shared document.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'token' => 'required|string', // Share access token
        ]);

        $share = Share::where('access_token', $request->token)->firstOrFail();
        
        // Ensure access is still valid (password checked in session)
        if ($share->password && !session()->has("share_unlocked_{$share->id}")) {
            return response()->json(['message' => 'Password required.'], 401);
        }

        // Check expiry
        if ($share->expires_at && $share->expires_at->isPast()) {
            return response()->json(['message' => 'Share has expired.'], 403);
        }

        $document = $share->shareable;
        if (!$document instanceof File) {
            return response()->json(['message' => 'Only files can be downloaded.'], 400);
        }

        // Create a temporary download token valid for 5 minutes
        $downloadToken = DownloadToken::create([
            'document_id' => $document->id,
            'token' => Str::random(60),
            'expired_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        return redirect()->route('download.execute', $downloadToken->token);
    }

    /**
     * Execute the download using the temporary token.
     */
    public function download($token)
    {
        $downloadToken = DownloadToken::where('token', $token)
            ->where('is_used', false)
            ->where('expired_at', '>', now())
            ->firstOrFail();

        $file = $downloadToken->document;
        
        // Mark token as used
        $downloadToken->update(['is_used' => true]);

        return redirect()->away($this->storageService->getDownloadUrl($file));
    }
}
