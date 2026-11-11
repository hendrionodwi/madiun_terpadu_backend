<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminDlh() || $user->isPetugasLapangan() || $user->isWarga();
    }

    public function view(User $user, Complaint $complaint): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        if ($user->isPetugasLapangan()) {
            return $complaint->assigned_petugas_id === $user->id;
        }

        if ($user->isWarga()) {
            return $complaint->pelapor_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isWarga();
    }

    public function appendInfo(User $user, Complaint $complaint): bool
    {
        return $user->isWarga() && $complaint->pelapor_id === $user->id;
    }

    public function verify(User $user, Complaint $complaint): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        return $user->isPetugasLapangan() && $complaint->assigned_petugas_id === $user->id;
    }

    public function assign(User $user, Complaint $complaint): bool
    {
        return $user->isAdminDlh();
    }

    public function finalize(User $user, Complaint $complaint): bool
    {
        return $user->isAdminDlh();
    }
}
