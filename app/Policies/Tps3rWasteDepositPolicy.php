<?php

namespace App\Policies;

use App\Models\Tps3rWasteDeposit;
use App\Models\User;

class Tps3rWasteDepositPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminDlh() || $user->isPengelolaTps3r() || $user->isPetugasLapangan();
    }

    public function view(User $user, Tps3rWasteDeposit $deposit): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        if ($user->isPengelolaTps3r()) {
            $profile = $user->pengelolaTps3rProfile;

            return $deposit->pengelola_id === $user->id
                || ($profile && $deposit->tps3r_profile_id === $profile->id);
        }

        if ($user->isPetugasLapangan()) {
            return $deposit->petugas_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminDlh() || $user->isPengelolaTps3r() || $user->isPetugasLapangan();
    }

    public function verify(User $user, Tps3rWasteDeposit $deposit): bool
    {
        if ($user->isAdminDlh()) {
            return true;
        }

        if ($user->isPengelolaTps3r()) {
            $profile = $user->pengelolaTps3rProfile;

            return $deposit->pengelola_id === $user->id
                || ($profile && $deposit->tps3r_profile_id === $profile->id);
        }

        return false;
    }
}
