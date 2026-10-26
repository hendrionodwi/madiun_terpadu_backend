<?php

namespace App\Models;

use Database\Factories\FundWithdrawalRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'source',
    'points_used',
    'amount',
    'destination_type',
    'destination_account',
    'status',
    'requester_note',
    'admin_note',
    'reviewed_by',
    'reviewed_at',
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
            'amount' => 'float',
            'reviewed_at' => 'datetime',
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
}
