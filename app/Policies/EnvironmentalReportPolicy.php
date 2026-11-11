<?php

namespace App\Policies;

use App\Models\EnvironmentalReport;
use App\Models\User;

class EnvironmentalReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminDlh() || $user->isPemrakarsa() || $user->isPetugasLapangan();
    }

    public function view(User $user, EnvironmentalReport $environmentalReport): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        if ($user->isPemrakarsa()) {
            return $environmentalReport->pemrakarsa_id === $user->id;
        }

        if ($user->isPetugasLapangan()) {
            return $environmentalReport->assigned_petugas_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isPemrakarsa();
    }

    public function update(User $user, EnvironmentalReport $environmentalReport): bool
    {
        return $user->isPemrakarsa() && $environmentalReport->pemrakarsa_id === $user->id;
    }

    public function delete(User $user, EnvironmentalReport $environmentalReport): bool
    {
        return $user->isPemrakarsa() && $environmentalReport->pemrakarsa_id === $user->id;
    }

    public function verify(User $user, EnvironmentalReport $environmentalReport): bool
    {
        return $user->isAdminDlh();
    }

    public function fieldCheck(User $user, EnvironmentalReport $environmentalReport): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        return $user->isPetugasLapangan() && $environmentalReport->assigned_petugas_id === $user->id;
    }
}
