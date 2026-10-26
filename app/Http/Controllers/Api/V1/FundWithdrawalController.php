<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateFundWithdrawalRequest;
use App\Http\Controllers\Controller;
use App\Models\FundWithdrawalRequest;
use App\Models\MasterData;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class FundWithdrawalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $withdrawals = FundWithdrawalRequest::query()->where('user_id', $user->id)->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $withdrawals->items(),
            'meta' => [
                'current_page' => $withdrawals->currentPage(),
                'last_page' => $withdrawals->lastPage(),
                'total' => $withdrawals->total(),
                'point_conversion_rate' => (float) (MasterData::query()
                    ->whereIn('group', ['point_conversion', 'point_conversions'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->value('numeric_value') ?? 10),
            ],
        ]);
    }

    public function store(Request $request, CreateFundWithdrawalRequest $action): JsonResponse
    {
        $validated = $request->validate([
            'source' => ['required', Rule::in(['points', 'store'])],
            'points_used' => ['nullable', 'integer', 'min:100'],
            'amount' => ['nullable', 'numeric', 'min:10000'],
            'destination_type' => ['required', Rule::in(['cash', 'e_wallet', 'retribution', 'bank_transfer'])],
            'destination_account' => ['nullable', 'string', 'max:100', 'required_if:destination_type,e_wallet,bank_transfer'],
            'requester_note' => ['nullable', 'string', 'max:500'],
        ]);
        /** @var User $user */
        $user = $request->user();
        $withdrawal = $action->handle($user, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan pencairan berhasil diajukan.',
            'data' => $withdrawal,
        ], Response::HTTP_CREATED);
    }
}
