<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ShareController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Folders
    Route::apiResource('folders', FolderController::class);

    // Files
    Route::get('files', [FileController::class, 'index'])->name('files.index');
    Route::get('files/upload-url', [FileController::class, 'getUploadUrl'])->name('files.upload-url');
    Route::post('files', [FileController::class, 'store'])->name('files.store');
    Route::get('files/{file}/preview', [FileController::class, 'preview'])->name('files.preview');
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::put('files/{file}', [FileController::class, 'update'])->name('files.update');
    Route::delete('files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

    // Shares
    Route::post('shares', [ShareController::class, 'store']);
    Route::get('shares/{tokenOrId}', [ShareController::class, 'show']);
    Route::delete('shares/{share}', [ShareController::class, 'destroy']);
});
