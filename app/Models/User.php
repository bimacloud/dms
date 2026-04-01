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

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    public function shares()
    {
        return $this->hasMany(Share::class, 'owner_id');
    }

    public function receivedShares()
    {
        return $this->hasMany(Share::class, 'shared_with_id');
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
        return (int) File::where('user_id', $this->id)->sum('size');
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
