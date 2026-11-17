<?php

namespace App\Http\Controllers\Web;

use App\Actions\CreateMarketplacePointSettlement;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFundWithdrawalRequest;
use App\Models\FundWithdrawalRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundWithdrawalController extends Controller
{
    public function index(
        Request $request,
        CreateMarketplacePointSettlement $action,
    ): View {
        /** @var User $user */
        $user = $request->user();

        $eligible = $action->eligibleSummary($user);

        return view('dashboard.tps3r.withdrawals', [
            'availableBalance' => $eligible['amount'],
            'eligibleFulfillments' => $eligible['fulfillments'],
            'withdrawals' => FundWithdrawalRequest::query()
                ->where('user_id', $user->id)
                ->whereIn('source', ['marketplace_points', 'store'])
                ->withCount('marketplaceSettlementItems')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function store(StoreFundWithdrawalRequest $request, CreateMarketplacePointSettlement $action): RedirectResponse
    {
        $validated = $request->validated();
        /** @var User $user */
        $user = $request->user();
        $action->handle($user, $validated);

        return back()->with('status', 'Settlement transaksi poin berhasil diajukan ke Admin DLH.');
    }
}
