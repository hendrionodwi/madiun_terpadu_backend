<?php

namespace App\Models;

use Database\Factories\MarketplaceSettlementItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'fund_withdrawal_request_id',
    'order_fulfillment_id',
    'amount',
    'released_at',
])]
class MarketplaceSettlementItem extends Model
{
    /** @use HasFactory<MarketplaceSettlementItemFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'released_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<FundWithdrawalRequest, $this> */
    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(FundWithdrawalRequest::class, 'fund_withdrawal_request_id');
    }

    /** @return BelongsTo<OrderFulfillment, $this> */
    public function fulfillment(): BelongsTo
    {
        return $this->belongsTo(OrderFulfillment::class, 'order_fulfillment_id');
    }
}
