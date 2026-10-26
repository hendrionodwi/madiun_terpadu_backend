<?php

namespace App\Actions;

use App\Enums\OrderFulfillmentStatus;
use App\Models\FundWithdrawalRequest;
use App\Models\MasterData;
use App\Models\OrderItem;
use App\Models\PointLedger;
use App\Models\RewardFundAllocation;
use App\Models\User;
use App\Models\WargaProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateFundWithdrawalRequest
{
    /** @param array<string, mixed> $data */
    public function handle(User $user, array $data): FundWithdrawalRequest
    {
        return DB::transaction(function () use ($user, $data): FundWithdrawalRequest {
            $source = (string) $data['source'];
            $pointsUsed = null;
            $amount = (float) ($data['amount'] ?? 0);

            if ($source === 'points') {
                if (! $user->isWarga()) {
                    throw ValidationException::withMessages(['source' => ['Penukaran poin hanya tersedia untuk warga.']]);
                }

                $profile = WargaProfile::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
                $pointsUsed = (int) ($data['points_used'] ?? 0);

                if ($pointsUsed < 100 || $pointsUsed > $profile->saldo_poin) {
                    throw ValidationException::withMessages(['points_used' => ['Jumlah poin minimal 100 dan tidak boleh melebihi saldo.']]);
                }

                $rate = (float) (MasterData::query()
                    ->whereIn('group', ['point_conversion', 'point_conversions'])
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->value('numeric_value') ?? 10);
                $amount = $pointsUsed * $rate;

                $availableBudget = $this->availableRewardBudget();
                if ($availableBudget < $amount) {
                    throw ValidationException::withMessages([
                        'points_used' => ['Alokasi saldo kas reward DLH saat ini sedang tidak mencukupi (tersedia: Rp '.number_format($availableBudget, 0, ',', '.').'). Silakan hubungi DLH atau coba nominal lebih kecil.'],
                    ]);
                }

            } elseif ($source === 'store') {
                if (! $user->isPengelolaTps3r()) {
                    throw ValidationException::withMessages(['source' => ['Pencairan pendapatan toko hanya tersedia untuk pengelola TPS3R.']]);
                }

                if ($amount < 10000 || $amount > $this->availableStoreBalance($user)) {
                    throw ValidationException::withMessages(['amount' => ['Nominal minimal Rp10.000 dan tidak boleh melebihi saldo toko tersedia.']]);
                }
            } else {
                throw ValidationException::withMessages(['source' => ['Sumber pencairan tidak valid.']]);
            }

            $withdrawal = FundWithdrawalRequest::create([
                'user_id' => $user->id,
                'source' => $source,
                'points_used' => $pointsUsed,
                'amount' => $amount,
                'destination_type' => $data['destination_type'],
                'destination_account' => $data['destination_account'] ?? null,
                'status' => 'pending',
                'requester_note' => $data['requester_note'] ?? null,
            ]);

            if ($source === 'points') {
                PointLedger::record(
                    $user->id,
                    'debit',
                    'penukaran_poin',
                    (int) $pointsUsed,
                    "Reservasi penukaran {$pointsUsed} poin ke saldo tunai/e-wallet",
                    $withdrawal
                );
            }

            return $withdrawal;
        });
    }

    public function availableStoreBalance(User $user): float
    {
        $grossRevenue = (float) OrderItem::query()
            ->whereHas('product', fn ($query) => $query->where('seller_id', $user->id))
            ->whereHas('order.fulfillments', fn ($query) => $query
                ->where('seller_id', $user->id)
                ->where('status', OrderFulfillmentStatus::Selesai->value))
            ->sum('subtotal');

        $reserved = (float) FundWithdrawalRequest::query()
            ->where('user_id', $user->id)
            ->where('source', 'store')
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        return max(0, $grossRevenue - $reserved);
    }

    public function availableRewardBudget(): float
    {
        $totalAllocated = (float) RewardFundAllocation::sum('amount');
        if ($totalAllocated <= 0) {
            return PHP_FLOAT_MAX;
        }

        $reserved = (float) FundWithdrawalRequest::query()
            ->where('source', 'points')
            ->whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        return max(0.0, $totalAllocated - $reserved);
    }
}
