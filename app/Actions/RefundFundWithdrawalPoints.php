<?php

namespace App\Actions;

use App\Models\FundWithdrawalRequest;
use App\Models\PointLedger;

class RefundFundWithdrawalPoints
{
    public function handle(FundWithdrawalRequest $withdrawal, string $reason): void
    {
        if ($withdrawal->source !== 'points' || (int) $withdrawal->points_used <= 0) {
            return;
        }

        $alreadyRefunded = PointLedger::query()
            ->where('warga_id', $withdrawal->user_id)
            ->where('kategori', 'refund_penukaran_poin')
            ->where('referensi_tipe', FundWithdrawalRequest::class)
            ->where('referensi_id', $withdrawal->id)
            ->exists();

        if ($alreadyRefunded) {
            return;
        }

        PointLedger::record(
            $withdrawal->user_id,
            'kredit',
            'refund_penukaran_poin',
            (int) $withdrawal->points_used,
            $reason,
            $withdrawal,
        );
    }
}
