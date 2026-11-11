<?php

namespace App\Policies;

use App\Models\RetributionBill;
use App\Models\User;

class RetributionBillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminDlh() || $user->isWarga();
    }

    public function view(User $user, RetributionBill $retributionBill): bool
    {
        return $user->isAdminDlh() || $retributionBill->warga_id === $user->id;
    }

    public function pay(User $user, RetributionBill $retributionBill): bool
    {
        return $user->isWarga() && $retributionBill->warga_id === $user->id;
    }

    public function confirmManual(User $user, RetributionBill $retributionBill): bool
    {
        return $user->isAdminDlh();
    }
}
