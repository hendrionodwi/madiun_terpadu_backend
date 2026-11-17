<?php

namespace App\Http\Controllers\Web;

use App\Actions\RedeemCashWithdrawalClaim;
use App\Http\Controllers\Controller;
use App\Http\Requests\RedeemCashWithdrawalRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class CashPointRedemptionController extends Controller
{
    public function store(
        RedeemCashWithdrawalRequest $request,
        RedeemCashWithdrawalClaim $action,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $withdrawal = $action->handle($user, $request->validated());

        return back()->with(
            'status',
            "Pencairan {$withdrawal->cash_claim_code} untuk {$withdrawal->user->name} sebesar Rp ".number_format($withdrawal->amount, 0, ',', '.').' telah dicatat.',
        );
    }
}
