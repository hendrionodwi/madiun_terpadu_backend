<?php

namespace App\Policies;

use App\Models\PublicDocument;
use App\Models\User;

class PublicDocumentPolicy
{
    /**
     * Determine whether the user can view any public documents in CMS.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can view the public document in CMS.
     */
    public function view(User $user, PublicDocument $publicDocument): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can upload public documents.
     */
    public function create(User $user): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can update the public document.
     */
    public function update(User $user, PublicDocument $publicDocument): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can delete the public document.
     */
    public function delete(User $user, PublicDocument $publicDocument): bool
    {
        return $user->isAdminDlh();
    }
}
