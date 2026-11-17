<?php

namespace App\Actions;

use App\Models\FundWithdrawalRequest;

class ReleaseMarketplaceSettlementItems
{
    public function handle(FundWithdrawalRequest $withdrawal): void
    {
        if ($withdrawal->source !== 'marketplace_points') {
            return;
        }

        $withdrawal->marketplaceSettlementItems()
            ->whereNull('released_at')
            ->update(['released_at' => now()]);
    }
}
