<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Category;
use App\Models\Share;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $fileCount = $isAdmin 
            ? File::count() 
            : File::where('user_id', $user->id)->count();

        $catCount = Category::count();

        $sharedCount = Share::where('shared_with_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->count();

        $recentFiles = $isAdmin 
            ? File::latest()->limit(5)->get()
            : File::where('user_id', $user->id)->latest()->limit(5)->get();

        $folderCount = $isAdmin
            ? \App\Models\Folder::count()
            : \App\Models\Folder::where('user_id', $user->id)->count();

        $recentFolders = $isAdmin
            ? \App\Models\Folder::latest()->limit(4)->get()
            : \App\Models\Folder::where('user_id', $user->id)->latest()->limit(4)->get();

        $storageUsed = $user->raw_disk_space_bytes;
        $storageQuota = $user->disk_quota;
        $storageUsedFormatted = $user->total_disk_space;
        $storageQuotaFormatted = $user->formatted_disk_quota;
        
        $storagePercentage = ($storageQuota > 0) ? min(100, round(($storageUsed / $storageQuota) * 100)) : 0;

        return view('dashboard', compact(
            'isAdmin',
            'fileCount',
            'folderCount',
            'catCount',
            'sharedCount',
            'recentFiles',
            'recentFolders',
            'storageUsedFormatted',
            'storageQuotaFormatted',
            'storagePercentage'
        ));
    }
}
