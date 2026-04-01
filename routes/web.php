<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\UserShareController;
use App\Http\Controllers\DriveController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FileMoveController;

// Auth Routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth', 'check.menu'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{file}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{file}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{file}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Drive UI
    Route::get('/drive/{folder?}', [DriveController::class, 'index'])->name('drive.index');
    Route::post('/drive/upload', [\App\Http\Controllers\UploadController::class, 'store'])->name('drive.upload');
    Route::post('/drive/complete', [\App\Http\Controllers\UploadController::class, 'complete'])->name('drive.complete');

    // Internal Sharing (Shared with me)
    Route::get('/shared-documents', [UserShareController::class, 'index'])->name('shared.index');
    Route::post('/shared-documents', [UserShareController::class, 'store'])->name('shared.store');
    Route::delete('/shared-documents/{share}', [UserShareController::class, 'destroy'])->name('shared.destroy');


    // Categories
    Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Menu Management
    Route::get('/settings/menus', [\App\Http\Controllers\MenuController::class, 'index'])->name('menus.index');
    Route::post('/settings/menus', [\App\Http\Controllers\MenuController::class, 'store'])->name('menus.store');
    Route::put('/settings/menus/{menu}', [\App\Http\Controllers\MenuController::class, 'update'])->name('menus.update');
    Route::delete('/settings/menus/{menu}', [\App\Http\Controllers\MenuController::class, 'destroy'])->name('menus.destroy');
    
    // User Management (Root and Admin)
    Route::middleware(['role:root,admin'])->group(function () {
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
        
        // System Config
        Route::get('/settings/company', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.company');
        Route::post('/settings/company', [\App\Http\Controllers\SettingController::class, 'store'])->name('settings.company.store');

        // Storage Providers
        Route::get('/settings/storage', [\App\Http\Controllers\StorageProviderController::class, 'index'])->name('settings.storage');
        Route::post('/settings/storage', [\App\Http\Controllers\StorageProviderController::class, 'store'])->name('settings.storage.store');
        Route::put('/settings/storage/{storageProvider}', [\App\Http\Controllers\StorageProviderController::class, 'update'])->name('settings.storage.update');
        Route::delete('/settings/storage/{storageProvider}', [\App\Http\Controllers\StorageProviderController::class, 'destroy'])->name('settings.storage.destroy');
    });
    Route::get('/reports', function() { return view('layouts.app')->with('content', 'Reports Page'); })->name('reports.index');
    Route::get('/network', function() { return view('layouts.app')->with('content', 'Network Status Page'); })->name('network.index');
    Route::get('/customers', function() { return view('layouts.app')->with('content', 'Customers Page'); })->name('customers.index');
    Route::get('/tickets', function() { return view('layouts.app')->with('content', 'Tickets Page'); })->name('tickets.index');
});

// Preview & Download (protected by auth but maybe bypass menu check for direct links)
Route::middleware(['auth'])->group(function () {
    Route::get('/documents/{file}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{file}/thumbnail', [DocumentController::class, 'thumbnail'])->name('documents.thumbnail');
    Route::get('/documents/{file}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::put('/documents/{file}', [DocumentController::class, 'update'])->name('web.files.update');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Folder Actions bypass menu check
    Route::post('/folders', [FolderController::class, 'store'])->name('web.folders.store');
    Route::put('/folders/{folder}', [FolderController::class, 'update'])->name('web.folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('web.folders.destroy');

    // Public Sharing Links Generation
    Route::post('/share-links', [ShareController::class, 'store'])->name('share_links.store');
    Route::delete('/share-links/{share}', [ShareController::class, 'destroy'])->name('share_links.destroy');

    // Generate download token (Internal bypass)
    Route::post('/documents/download-token', [DownloadController::class, 'generate'])->name('download.generate.auth');
});

// Advanced Public Sharing Options
Route::get('/share/{token}', [ShareController::class, 'show'])->name('share.show');
Route::get('/share/{token}/preview', [ShareController::class, 'preview'])->name('share.preview');
Route::post('/share/{token}/password', [ShareController::class, 'verifyPassword'])->name('share.password.verify');

// Public Generating temp download token from Share Link
Route::post('/share/download-token', [DownloadController::class, 'generate'])->name('download.generate.public');

// Execute Temporary Download
Route::get('/download/{token}', [DownloadController::class, 'download'])->name('download.execute');
