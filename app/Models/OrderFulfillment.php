<?php

namespace App\Models;

use App\Enums\OrderFulfillmentStatus;
use Database\Factories\OrderFulfillmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $seller_id
 * @property OrderFulfillmentStatus $status
 * @property string|null $catatan_status
 * @property Carbon|null $processed_at
 * @property Carbon|null $ready_at
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
    'processed_at',
    'ready_at',
    'completed_at',
    'cancelled_at',
])]
class OrderFulfillment extends Model
{
    /** @use HasFactory<OrderFulfillmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => OrderFulfillmentStatus::class,
            'processed_at' => 'datetime',
            'ready_at' => 'datetime',
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
}
