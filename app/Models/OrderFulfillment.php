<?php

namespace App\Models;

use App\Enums\OrderFulfillmentStatus;
use Database\Factories\OrderFulfillmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property int $order_id
 * @property int $seller_id
 * @property OrderFulfillmentStatus $status
 * @property string|null $catatan_status
 * @property string|null $tracking_number
 * @property string|null $courier_status
 * @property string|null $courier_status_description
 * @property Carbon|null $tracking_checked_at
 * @property Carbon|null $next_tracking_check_at
 * @property string|null $tracking_last_error
 * @property Carbon|null $delivered_at
 * @property Carbon|null $auto_complete_at
 * @property Carbon|null $processed_at
 * @property Carbon|null $ready_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read User $seller
 */
#[Fillable([
    'order_id',
    'seller_id',
    'status',
    'catatan_status',
    'tracking_number',
    'courier_status',
    'courier_status_description',
    'tracking_checked_at',
    'next_tracking_check_at',
    'tracking_last_error',
    'delivered_at',
    'auto_complete_at',
    'processed_at',
    'ready_at',
    'shipped_at',
    'completed_at',
    'cancelled_at',
])]
class OrderFulfillment extends Model
{
    /** @use HasFactory<OrderFulfillmentFactory> */
    use HasFactory, HasUlids;

    /** @var list<string> */
    protected $hidden = ['tracking_last_error'];

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => OrderFulfillmentStatus::class,
            'tracking_checked_at' => 'datetime',
            'next_tracking_check_at' => 'datetime',
            'delivered_at' => 'datetime',
            'auto_complete_at' => 'datetime',
            'processed_at' => 'datetime',
            'ready_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /** @return HasMany<MarketplaceSettlementItem, $this> */
    public function marketplaceSettlementItems(): HasMany
    {
        return $this->hasMany(MarketplaceSettlementItem::class);
    }
}
