<?php

namespace App\Policies;

use App\Models\User;

class PortalSettingPolicy
{
    /**
     * Determine whether the user can view CMS portal settings.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can update CMS portal settings.
     */
    public function update(User $user): bool
    {
        return $user->isAdminDlh();
    }
}
