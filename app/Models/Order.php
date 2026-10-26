<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nomor_pesanan
 * @property int $buyer_id
 * @property float $total_harga
 * @property string $metode_pembayaran
 * @property string $status
 * @property string|null $alamat_pengiriman
 * @property string|null $catatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $buyer
 * @property-read Collection<int, OrderItem> $items
 * @property-read Collection<int, OrderFulfillment> $fulfillments
 */
#[Fillable([
    'nomor_pesanan',
    'buyer_id',
    'total_harga',
    'metode_pembayaran',
    'status',
    'alamat_pengiriman',
    'catatan',
    'snap_token',
    'snap_redirect_url',
    'payment_gateway_response',
    'paid_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /** @var list<string> */
    public const REVENUE_STATUSES = ['dibayar', 'diproses', 'siap_dikirim', 'dikirim', 'selesai'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_harga' => 'float',
            'payment_gateway_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Relation to Buyer User.
     *
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Relation to Order Items.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /** @return HasMany<OrderFulfillment, $this> */
    public function fulfillments(): HasMany
    {
        return $this->hasMany(OrderFulfillment::class);
    }
}
