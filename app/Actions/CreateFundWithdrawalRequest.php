<?php

namespace App\Actions;

use App\Enums\FundWithdrawalStatus;
use App\Models\FundWithdrawalRequest;
use App\Models\PointLedger;
use App\Models\RewardFundAllocation;
use App\Models\User;
use App\Models\WargaProfile;
use App\Services\PointConversionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateFundWithdrawalRequest
{
    public function __construct(private PointConversionService $pointConversion) {}

    /** @param array<string, mixed> $data */
    public function handle(User $user, array $data): FundWithdrawalRequest
    {
        return DB::transaction(function () use ($user, $data): FundWithdrawalRequest {
            $source = (string) $data['source'];
            if ($source !== 'points') {
                throw ValidationException::withMessages(['source' => ['Sumber pencairan tidak valid.']]);
            }

            if (! $user->isWarga()) {
                throw ValidationException::withMessages(['source' => ['Penukaran poin hanya tersedia untuk warga.']]);
            }

            $profile = WargaProfile::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $pointsUsed = (int) ($data['points_used'] ?? 0);

            $minimumPoints = (int) config('finance.points.minimum_redemption_points');
            if ($pointsUsed < $minimumPoints || $pointsUsed > $profile->saldo_poin) {
                throw ValidationException::withMessages([
                    'points_used' => ['Jumlah poin minimal '.number_format($minimumPoints, 0, ',', '.').' dan tidak boleh melebihi saldo.'],
                ]);
            }

            $rate = $this->pointConversion->currentRate();
            $amount = $this->pointConversion->convert($pointsUsed);

            $availableBudget = $this->availableRewardBudget();
            if ($availableBudget < $amount) {
                throw ValidationException::withMessages([
                    'points_used' => ['Alokasi saldo kas reward DLH saat ini sedang tidak mencukupi (tersedia: Rp '.number_format($availableBudget, 0, ',', '.').'). Silakan hubungi DLH atau coba nominal lebih kecil.'],
                ]);
            }

            $withdrawal = FundWithdrawalRequest::create([
                'user_id' => $user->id,
                'source' => $source,
                'points_used' => $pointsUsed,
                'point_conversion_rate' => $rate,
                'amount' => $amount,
                'destination_type' => $data['destination_type'],
                'destination_provider' => $data['destination_provider'] ?? null,
                'destination_account' => $data['destination_account'] ?? null,
                'destination_account_name' => $data['destination_account_name'] ?? null,
                'cash_pickup_location' => $data['destination_type'] === 'cash'
                    ? config('finance.points.cash_pickup_location')
                    : null,
                'cash_claim_code' => $data['destination_type'] === 'cash'
                    ? $this->generateCashClaimCode()
                    : null,
                'status' => FundWithdrawalStatus::Pending->value,
                'requester_note' => $data['requester_note'] ?? null,
            ]);

            PointLedger::record(
                $user->id,
                'debit',
                'penukaran_poin',
                $pointsUsed,
                "Reservasi penukaran {$pointsUsed} poin ke saldo tunai/e-wallet",
                $withdrawal
            );

            return $withdrawal;
        });
    }

    private function generateCashClaimCode(): string
    {
        do {
            $code = 'TUN-'.Str::upper(Str::random(5)).'-'.Str::upper(Str::random(5));
        } while (FundWithdrawalRequest::query()->where('cash_claim_code', $code)->exists());

        return $code;
    }

    public function availableRewardBudget(): float
    {
        $totalAllocated = (float) RewardFundAllocation::sum('amount');
        if ($totalAllocated <= 0) {
            return 0.0;
        }

        $reserved = (float) FundWithdrawalRequest::query()
            ->whereIn('source', ['points', 'marketplace_points'])
            ->whereIn('status', ['pending', 'approved', 'processing', 'paid'])
            ->sum('amount');

        return max(0.0, $totalAllocated - $reserved);
    }
}
