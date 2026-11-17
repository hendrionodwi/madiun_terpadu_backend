<?php

namespace App\Models;

use Database\Factories\FundWithdrawalRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'reward_fund_allocation_id',
    'source',
    'points_used',
    'point_conversion_rate',
    'amount',
    'destination_type',
    'destination_provider',
    'destination_account',
    'destination_account_name',
    'cash_pickup_location',
    'cash_claim_code',
    'status',
    'requester_note',
    'admin_note',
    'payout_reference',
    'payout_proof_path',
    'payout_note',
    'failure_note',
    'reviewed_by',
    'processed_by',
    'cash_claimed_by',
    'reviewed_at',
    'processed_at',
    'paid_at',
    'cash_claimed_at',
    'failed_at',
])]
class FundWithdrawalRequest extends Model
{
    /** @use HasFactory<FundWithdrawalRequestFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'points_used' => 'integer',
            'point_conversion_rate' => 'float',
            'amount' => 'float',
            'reviewed_at' => 'datetime',
            'processed_at' => 'datetime',
            'paid_at' => 'datetime',
            'cash_claimed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function cashClaimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cash_claimed_by');
    }

    /** @return BelongsTo<RewardFundAllocation, $this> */
    public function fundAllocation(): BelongsTo
    {
        return $this->belongsTo(RewardFundAllocation::class, 'reward_fund_allocation_id');
    }

    /** @return HasMany<MarketplaceSettlementItem, $this> */
    public function marketplaceSettlementItems(): HasMany
    {
        return $this->hasMany(MarketplaceSettlementItem::class);
    }
}
