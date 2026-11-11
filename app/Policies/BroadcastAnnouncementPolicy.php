<?php

namespace App\Policies;

use App\Models\BroadcastAnnouncement;
use App\Models\User;

class BroadcastAnnouncementPolicy
{
    /**
     * Determine whether the user can view any CMS announcements.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can view the CMS announcement.
     */
    public function view(User $user, BroadcastAnnouncement $broadcastAnnouncement): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can create CMS announcements.
     */
    public function create(User $user): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can update the CMS announcement.
     */
    public function update(User $user, BroadcastAnnouncement $broadcastAnnouncement): bool
    {
        return $user->isAdminDlh();
    }

    /**
     * Determine whether the user can delete the CMS announcement.
     */
    public function delete(User $user, BroadcastAnnouncement $broadcastAnnouncement): bool
    {
        return $user->isAdminDlh();
    }
}
