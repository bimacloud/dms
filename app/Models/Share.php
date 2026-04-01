<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Share extends Model
{
    use HasUuids;

    protected $fillable = [
        'shareable_type',
        'shareable_id',
        'owner_id',
        'shared_with_id',
        'permission',
        'access_token',
        'password',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the parent shareable model (File or Folder).
     */
    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who owns the share.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the user the file is shared with (null for public links).
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_id');
    }

    /* --- Compatibility Aliases for Views --- */

    public function getTokenAttribute()
    {
        return $this->access_token;
    }

    public function getDocumentAttribute()
    {
        return $this->shareable_type === File::class ? $this->shareable : null;
    }

    public function getCreatorAttribute()
    {
        return $this->owner;
    }

    public function getExpiredAtAttribute()
    {
        return $this->expires_at;
    }
}
