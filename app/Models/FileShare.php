<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    protected $fillable = [
        'document_id',
        'token',
        'password',
        'expired_at',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
