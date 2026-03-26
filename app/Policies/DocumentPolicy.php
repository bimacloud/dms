<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Models\FileUserShare;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $user->id === $document->uploaded_by || 
               $user->isAdmin() ||
               FileUserShare::where('document_id', $document->id)->where('shared_to', $user->id)->exists();
    }

    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->uploaded_by || $user->isAdmin();
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->uploaded_by || $user->isAdmin();
    }
}
