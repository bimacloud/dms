<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;
use App\Models\Share;

class FolderPolicy
{
    public function view(User $user, Folder $folder): bool
    {
        if ($user->isAdmin() || $user->id === $folder->user_id) {
            return true;
        }

        return Share::where('shareable_type', Folder::class)
            ->where('shareable_id', $folder->id)
            ->where(function ($query) use ($user) {
                $query->where('shared_with_id', $user->id)
                      ->orWhereNull('shared_with_id'); // Public share
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    public function update(User $user, Folder $folder): bool
    {
        return $user->isAdmin() || $user->id === $folder->user_id;
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $user->isAdmin() || $user->id === $folder->user_id;
    }
}
