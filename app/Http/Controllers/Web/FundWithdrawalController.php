<?php

namespace App\Http\Controllers\Web;

use App\Actions\CreateFundWithdrawalRequest;
use App\Http\Controllers\Controller;
use App\Models\FundWithdrawalRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FundWithdrawalController extends Controller
{
    public function index(Request $request, CreateFundWithdrawalRequest $action): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('dashboard.tps3r.withdrawals', [
            'availableBalance' => $action->availableStoreBalance($user),
            'withdrawals' => FundWithdrawalRequest::query()
                ->where('user_id', $user->id)
                ->where('source', 'store')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function store(Request $request, CreateFundWithdrawalRequest $action): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'destination_type' => ['required', Rule::in(['bank_transfer', 'e_wallet'])],
            'destination_account' => ['required', 'string', 'max:100'],
            'requester_note' => ['nullable', 'string', 'max:500'],
        ]);
        /** @var User $user */
        $user = $request->user();
        $action->handle($user, [...$validated, 'source' => 'store']);

        return back()->with('status', 'Permintaan pencairan pendapatan berhasil diajukan.');
    }
}
