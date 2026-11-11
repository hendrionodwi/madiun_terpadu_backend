<?php

namespace App\Policies;

use App\Models\FundWithdrawalRequest;
use App\Models\User;

class FundWithdrawalRequestPolicy
{
    public function view(User $user, FundWithdrawalRequest $fundWithdrawalRequest): bool
    {
        return $user->isAdminDlh() || $fundWithdrawalRequest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isWarga() || $user->isPengelolaTps3r();
    }

    public function review(User $user, FundWithdrawalRequest $fundWithdrawalRequest): bool
    {
        return $user->isAdminDlh();
    }
}
