<?php

namespace App\Policies;

use App\Models\PickupSchedule;
use App\Models\User;

class PickupSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PickupSchedule $pickupSchedule): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        if ($user->isPetugasLapangan()) {
            return $pickupSchedule->petugas_id === $user->id;
        }

        if ($user->isWarga()) {
            return $pickupSchedule->warga_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminDlh() || $user->isPetugasLapangan() || $user->isWarga();
    }

    public function update(User $user, PickupSchedule $pickupSchedule): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        return $pickupSchedule->warga_id === $user->id || $pickupSchedule->petugas_id === $user->id;
    }

    public function updateStatus(User $user, PickupSchedule $pickupSchedule): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        return $user->isPetugasLapangan() && $pickupSchedule->petugas_id === $user->id;
    }

    public function delete(User $user, PickupSchedule $pickupSchedule): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        return $user->isWarga() && $pickupSchedule->warga_id === $user->id;
    }
}
