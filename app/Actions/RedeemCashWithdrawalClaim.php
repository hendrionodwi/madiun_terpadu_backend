<?php

namespace App\Actions;

use App\Enums\FundWithdrawalStatus;
use App\Models\FundWithdrawalRequest;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RedeemCashWithdrawalClaim
{
    /** @param array<string, mixed> $data */
    public function handle(User $operator, array $data): FundWithdrawalRequest
    {
        return DB::transaction(function () use ($operator, $data): FundWithdrawalRequest {
            if (! $operator->isAdminDlh()) {
                throw ValidationException::withMessages([
                    'claim_code' => ['Pencairan tunai hanya dapat diselesaikan oleh Admin DLH.'],
                ]);
            }

            $withdrawal = FundWithdrawalRequest::query()
                ->where('cash_claim_code', $data['claim_code'])
                ->lockForUpdate()
                ->first();

            if ($withdrawal === null) {
                throw ValidationException::withMessages([
                    'claim_code' => ['Kode transaksi tunai tidak ditemukan.'],
                ]);
            }

            if ($withdrawal->source !== 'points' || $withdrawal->destination_type !== 'cash') {
                throw ValidationException::withMessages([
                    'claim_code' => ['Kode ini bukan transaksi penukaran poin tunai.'],
                ]);
            }

            if ($withdrawal->status !== FundWithdrawalStatus::Approved->value) {
                $message = $withdrawal->status === FundWithdrawalStatus::Paid->value
                    ? 'Kode transaksi ini sudah pernah dicairkan.'
                    : 'Kode transaksi belum aktif atau tidak dapat dicairkan.';

                throw ValidationException::withMessages(['claim_code' => [$message]]);
            }

            if ($withdrawal->reward_fund_allocation_id === null) {
                throw ValidationException::withMessages([
                    'claim_code' => ['Sumber dana kas reward belum ditetapkan oleh Admin DLH.'],
                ]);
            }

            $proof = $data['payout_proof'] ?? null;
            if (! $proof instanceof UploadedFile) {
                throw ValidationException::withMessages([
                    'payout_proof' => ['Bukti serah-terima tunai wajib diunggah.'],
                ]);
            }

            $proofPath = $proof->store('fund-withdrawal-proofs', 'local');
            $claimedAt = now();
            $note = 'Identitas warga diverifikasi dan uang tunai diserahkan melalui loket Admin DLH.';
            if (filled($data['payout_note'] ?? null)) {
                $note .= ' '.$data['payout_note'];
            }

            $withdrawal->update([
                'status' => FundWithdrawalStatus::Paid->value,
                'payout_reference' => $data['payout_reference'],
                'payout_proof_path' => $proofPath,
                'payout_note' => $note,
                'processed_by' => $operator->id,
                'processed_at' => $claimedAt,
                'paid_at' => $claimedAt,
                'cash_claimed_by' => $operator->id,
                'cash_claimed_at' => $claimedAt,
            ]);

            return $withdrawal->fresh([
                'user:id,name,email,phone_number',
                'cashClaimedBy:id,name',
                'fundAllocation',
            ]);
        });
    }
}
