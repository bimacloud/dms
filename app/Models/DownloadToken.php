<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Get the document associated with the token.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(File::class, 'document_id');
    }
}
