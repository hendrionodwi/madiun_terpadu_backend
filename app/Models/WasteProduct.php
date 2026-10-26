<?php

namespace App\Models;

use Database\Factories\WasteProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $seller_id
 * @property string $nama_produk
 * @property string|null $deskripsi
 * @property string $kategori
 * @property float $harga
 * @property int $stok
 * @property string|null $foto
 * @property bool $is_available
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $seller
 */
#[Fillable([
    'seller_id',
    'nama_produk',
    'deskripsi',
    'kategori',
    'harga',
    'stok',
    'foto',
    'is_available',
])]
class WasteProduct extends Model
{
    /** @use HasFactory<WasteProductFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga' => 'float',
            'stok' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    /**
     * Relation to Seller User.
     *
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Relation to Order Items.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }
}
