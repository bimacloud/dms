<?php

namespace App\Http\Controllers;

use App\Models\FileShare;
use App\Models\DownloadToken;
use App\Models\FileUserShare;
use App\Models\Document;
use App\Services\FileStorageService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    protected $storageService;

    public function __construct(FileStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    /**
     * Generate a temporary download link.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'token' => 'nullable|string', // Public share token
            'document_id' => 'nullable|exists:documents,id' // For internal sharing
        ]);

        $document = null;

        // Verify if the user has access via public share link
        if ($request->filled('token')) {
            $share = FileShare::where('token', $request->token)->firstOrFail();
            if (!$share->is_active || ($share->expired_at && now()->greaterThan($share->expired_at))) {
                abort(403, 'Link expired or revoked.');
            }
            if ($share->password && !session()->has("share_{$share->token}_unlocked")) {
                abort(403, 'Password required.');
            }
            $document = $share->document;
        } 
        // Or if the user has access via internal sharing or owns the file
        elseif ($request->filled('document_id') && auth()->check()) {
            $docId = $request->document_id;
            $doc = Document::findOrFail($docId);
            
            $hasAccess = (
                auth()->user()->role->name === 'root' || 
                $doc->uploaded_by === auth()->id() ||
                FileUserShare::where('document_id', $doc->id)->where('shared_to', auth()->id())->exists()
            );

            if (!$hasAccess) {
                abort(403, 'Unauthorized.');
            }
            $document = $doc;
        } else {
            abort(400, 'Invalid request.');
        }

        // Generate temporary token (expires in 60 seconds)
        $downloadToken = DownloadToken::create([
            'document_id' => $document->id,
            'token' => Str::random(60),
            'expired_at' => now()->addSeconds(60),
            'is_used' => false,
        ]);

        return redirect()->route('download.execute', $downloadToken->token);
    }

    /**
     * Execute the actual download using temporary token.
     */
    public function download($token)
    {
        $downloadToken = DownloadToken::with('document')->where('token', $token)->firstOrFail();

        if ($downloadToken->is_used) {
            abort(403, 'This download link has already been used.');
        }

        if (now()->greaterThan($downloadToken->expired_at)) {
            abort(403, 'This download link has expired.');
        }

        $document = $downloadToken->document;

        if (!$this->storageService->exists($document->file_path)) {
            abort(404, 'File not found on server.');
        }

        // Mark as used
        $downloadToken->update(['is_used' => true]);

        $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
        $fileName = \Illuminate\Support\Str::finish($document->title, '.' . $extension);

        return $this->storageService->download($document->file_path, $fileName);
    }
}
