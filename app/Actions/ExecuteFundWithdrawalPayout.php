<?php

namespace App\Actions;

use App\Enums\FundWithdrawalStatus;
use App\Models\FundWithdrawalRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExecuteFundWithdrawalPayout
{
    public function __construct(
        private RefundFundWithdrawalPoints $refundPoints,
        private ReleaseMarketplaceSettlementItems $releaseMarketplaceSettlementItems,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $operator, FundWithdrawalRequest $withdrawal, array $data): FundWithdrawalRequest
    {
        return DB::transaction(function () use ($operator, $withdrawal, $data): FundWithdrawalRequest {
            $lockedWithdrawal = FundWithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);
            $targetStatus = FundWithdrawalStatus::from((string) $data['status']);

            if ($lockedWithdrawal->destination_type === 'cash' && $targetStatus !== FundWithdrawalStatus::Failed) {
                throw ValidationException::withMessages([
                    'status' => ['Pencairan tunai harus diselesaikan melalui loket Admin DLH menggunakan kode klaim warga.'],
                ]);
            }

            $currentStatus = FundWithdrawalStatus::from($lockedWithdrawal->status);
            if (! $currentStatus->canTransitionTo($targetStatus)) {
                throw ValidationException::withMessages([
                    'status' => ['Perubahan status pembayaran tidak sesuai urutan proses.'],
                ]);
            }

            $proofPath = $lockedWithdrawal->payout_proof_path;
            if (($data['payout_proof'] ?? null) instanceof UploadedFile) {
                if ($proofPath !== null) {
                    Storage::disk('local')->delete($proofPath);
                }

                $proofPath = $data['payout_proof']->store('fund-withdrawal-proofs', 'local');
            }

            if ($targetStatus === FundWithdrawalStatus::Failed) {
                $this->refundPoints->handle(
                    $lockedWithdrawal,
                    'Pengembalian poin karena pembayaran pencairan gagal diselesaikan.',
                );
                $this->releaseMarketplaceSettlementItems->handle($lockedWithdrawal);
            }

            $timestampUpdates = match ($targetStatus) {
                FundWithdrawalStatus::Processing => ['processed_at' => now()],
                FundWithdrawalStatus::Paid => ['paid_at' => now()],
                FundWithdrawalStatus::Failed => ['failed_at' => now()],
                default => [],
            };

            $lockedWithdrawal->update([
                'status' => $targetStatus->value,
                'payout_reference' => $data['payout_reference'] ?? $lockedWithdrawal->payout_reference,
                'payout_proof_path' => $proofPath,
                'payout_note' => $data['payout_note'] ?? $lockedWithdrawal->payout_note,
                'failure_note' => $data['failure_note'] ?? null,
                'processed_by' => $operator->id,
                ...$timestampUpdates,
            ]);

            return $lockedWithdrawal->fresh(['user:id,name,email', 'reviewer:id,name', 'processor:id,name', 'fundAllocation']);
        });
    }
}
