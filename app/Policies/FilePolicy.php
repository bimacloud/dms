<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use App\Models\Share;
use Illuminate\Auth\Access\Response;

class FilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, File $file): bool
    {
        if ($user->isAdmin() || $user->id === $file->user_id) {
            return true;
        }

        // Direct share
        $hasDirectShare = Share::where('shareable_type', File::class)
            ->where('shareable_id', $file->id)
            ->where(function ($query) use ($user) {
                $query->where('shared_with_id', $user->id)
                      ->orWhereNull('shared_with_id');
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasDirectShare) {
            return true;
        }

        // Parent folder share
        if ($file->folder_id) {
            return $user->can('view', $file->folder);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, File $file): bool
    {
        return $user->isAdmin() || $user->id === $file->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, File $file): bool
    {
        return $user->isAdmin() || $user->id === $file->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, File $file): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, File $file): bool
    {
        return false;
    }
}
