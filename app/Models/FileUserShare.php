<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileUserShare extends Model
{
    protected $fillable = [
        'document_id',
        'shared_by',
        'shared_to',
        'permission',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function sharedTo()
    {
        return $this->belongsTo(User::class, 'shared_to');
    }
}
