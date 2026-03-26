<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'file_type',
        'category_id',
        'folder_id',
        'uploaded_by',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function fileShares()
    {
        return $this->hasMany(FileShare::class);
    }

    public function userShares()
    {
        return $this->hasMany(FileUserShare::class);
    }

    public function downloadTokens()
    {
        return $this->hasMany(DownloadToken::class);
    }
}
