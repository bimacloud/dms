<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'position_id',
        'disk_quota',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    public function sharedDocuments()
    {
        return $this->hasMany(FileUserShare::class, 'shared_by');
    }

    public function receivedDocuments()
    {
        return $this->hasMany(FileUserShare::class, 'shared_to');
    }

    public function fileShares()
    {
        return $this->hasMany(FileShare::class, 'created_by');
    }

    public function isRoot(): bool
    {
        return $this->role && $this->role->name === 'root';
    }

    public function isAdmin(): bool
    {
        return $this->role && in_array($this->role->name, ['root', 'admin']);
    }

    public function getRawDiskSpaceBytesAttribute(): int
    {
        $documents = Document::where('uploaded_by', $this->id)->get();
        $totalBytes = 0;
        foreach ($documents as $doc) {
            $path = $doc->file_path;
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                $totalBytes += \Illuminate\Support\Facades\Storage::disk('public')->size($path);
            }
        }
        return (int) $totalBytes;
    }

    public function getTotalDiskSpaceAttribute()
    {
        $totalBytes = $this->raw_disk_space_bytes;
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($totalBytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $bytes /= (1 << (10 * $pow)); 
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function getFormattedDiskQuotaAttribute(): string
    {
        if (is_null($this->disk_quota) || $this->disk_quota === 0) {
            return 'Unlimited';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($this->disk_quota, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $bytes /= (1 << (10 * $pow)); 
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function hasAvailableDiskSpace(int $newFileBytes): bool
    {
        if (is_null($this->disk_quota) || $this->disk_quota === 0) {
            return true;
        }

        return ($this->raw_disk_space_bytes + $newFileBytes) <= $this->disk_quota;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
