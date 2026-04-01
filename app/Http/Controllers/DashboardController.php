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

        return view('dashboard', compact(
            'isAdmin',
            'fileCount',
            'catCount',
            'sharedCount',
            'recentFiles'
        ));
    }
}
