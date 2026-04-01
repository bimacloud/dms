<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'folder_id',
        'user_id',
        'category_id',
        'storage_provider_id',
        'display_name',
        'storage_path',
        'mime_type',
        'size',
        'extension',
        'disk',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
    ];

    /**
     * Get the user that owns the file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the folder that contains the file.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function storageProvider(): BelongsTo
    {
        return $this->belongsTo(StorageProvider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all of the file's shares.
     */
    public function shares(): MorphMany
    {
        return $this->morphMany(Share::class, 'shareable');
    }

    public function getTitleAttribute()
    {
        return $this->display_name;
    }
}
