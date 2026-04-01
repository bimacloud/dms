<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageProvider extends Model
{
    protected $fillable = [
        'name',
        'driver',
        'key',
        'secret',
        'region',
        'bucket',
        'endpoint',
        'use_path_style_endpoint',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'use_path_style_endpoint' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'secret' => 'encrypted',
    ];

    public function files()
    {
        return $this->hasMany(File::class);
    }
}
