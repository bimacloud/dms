<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadToken extends Model
{
    protected $fillable = [
        'document_id',
        'token',
        'expired_at',
        'is_used',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
