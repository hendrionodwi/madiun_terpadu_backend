<?php

namespace App\Actions;

use App\Models\FundWithdrawalRequest;
use App\Models\PointLedger;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewFundWithdrawalRequest
{
    public function handle(User $admin, FundWithdrawalRequest $withdrawal, string $status, ?string $adminNote): FundWithdrawalRequest
    {
        return DB::transaction(function () use ($admin, $withdrawal, $status, $adminNote): FundWithdrawalRequest {
            $lockedWithdrawal = FundWithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);

            if ($lockedWithdrawal->status !== 'pending') {
                throw ValidationException::withMessages(['status' => ['Permintaan ini sudah ditinjau sebelumnya.']]);
            }

            if ($status === 'rejected' && $lockedWithdrawal->source === 'points') {
                PointLedger::record(
                    $lockedWithdrawal->user_id,
                    'kredit',
                    'refund_penukaran_poin',
                    (int) ($lockedWithdrawal->points_used ?? 0),
                    'Pengembalian poin karena permintaan pencairan ditolak Admin DLH.',
                    $lockedWithdrawal
                );
            }

            $lockedWithdrawal->update([
                'status' => $status,
                'admin_note' => $adminNote,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $lockedWithdrawal->fresh(['user:id,name,email', 'reviewer:id,name']);
        });
    }
}
