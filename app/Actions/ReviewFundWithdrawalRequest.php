<?php

namespace App\Actions;

use App\Enums\FundWithdrawalStatus;
use App\Models\FundWithdrawalRequest;
use App\Models\RewardFundAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewFundWithdrawalRequest
{
    public function __construct(
        private RefundFundWithdrawalPoints $refundPoints,
        private ReleaseMarketplaceSettlementItems $releaseMarketplaceSettlementItems,
    ) {}

    public function handle(
        User $admin,
        FundWithdrawalRequest $withdrawal,
        FundWithdrawalStatus $status,
        ?string $adminNote,
        ?int $allocationId = null,
    ): FundWithdrawalRequest {
        return DB::transaction(function () use ($admin, $withdrawal, $status, $adminNote, $allocationId): FundWithdrawalRequest {
            $lockedWithdrawal = FundWithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);

            if ($lockedWithdrawal->status !== FundWithdrawalStatus::Pending->value) {
                throw ValidationException::withMessages(['status' => ['Permintaan ini sudah ditinjau sebelumnya.']]);
            }

            $allocation = null;
            if ($status === FundWithdrawalStatus::Approved && in_array($lockedWithdrawal->source, ['points', 'marketplace_points'], true)) {
                $allocation = RewardFundAllocation::query()->lockForUpdate()->findOrFail($allocationId);
                $committedAmount = (float) FundWithdrawalRequest::query()
                    ->where('reward_fund_allocation_id', $allocation->id)
                    ->whereIn('status', [
                        FundWithdrawalStatus::Approved->value,
                        FundWithdrawalStatus::Processing->value,
                        FundWithdrawalStatus::Paid->value,
                    ])
                    ->sum('amount');

                if (($committedAmount + (float) $lockedWithdrawal->amount) > (float) $allocation->amount) {
                    throw ValidationException::withMessages([
                        'reward_fund_allocation_id' => ['Sisa alokasi dana yang dipilih tidak mencukupi untuk permintaan ini.'],
                    ]);
                }
            }

            if ($status === FundWithdrawalStatus::Rejected) {
                $this->refundPoints->handle(
                    $lockedWithdrawal,
                    'Pengembalian poin karena permintaan pencairan ditolak Admin DLH.',
                );
                $this->releaseMarketplaceSettlementItems->handle($lockedWithdrawal);
            }

            $lockedWithdrawal->update([
                'status' => $status->value,
                'reward_fund_allocation_id' => $allocation?->id,
                'admin_note' => $adminNote,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $lockedWithdrawal->fresh(['user:id,name,email', 'reviewer:id,name', 'fundAllocation']);
        });
    }
}
