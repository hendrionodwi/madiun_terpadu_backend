<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateFundWithdrawalRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFundWithdrawalRequest;
use App\Models\FundWithdrawalRequest;
use App\Models\User;
use App\Services\PointConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FundWithdrawalController extends Controller
{
    public function index(Request $request, PointConversionService $pointConversion): JsonResponse
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
                'point_conversion_rate' => $pointConversion->currentRate(),
                'minimum_redemption_points' => (int) config('finance.points.minimum_redemption_points'),
            ],
        ]);
    }

    public function store(StoreFundWithdrawalRequest $request, CreateFundWithdrawalRequest $action): JsonResponse
    {
        $validated = $request->validated();
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
