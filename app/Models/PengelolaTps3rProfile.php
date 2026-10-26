<?php

namespace App\Models;

use Database\Factories\PengelolaTps3rProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $nama_tps3r
 * @property string|null $kode_tps3r
 * @property string|null $alamat
 * @property string|null $kelurahan
 * @property string|null $kecamatan
 * @property float|null $kapasitas_harian_kg
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'nama_tps3r',
    'kode_tps3r',
    'alamat',
    'kelurahan',
    'kecamatan',
    'kapasitas_harian_kg',
    'latitude',
    'longitude',
    'midtrans_merchant_id',
    'midtrans_client_key',
    'midtrans_server_key',
    'midtrans_is_production',
    'midtrans_is_active',
])]
class PengelolaTps3rProfile extends Model
{
    /** @use HasFactory<PengelolaTps3rProfileFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kapasitas_harian_kg' => 'float',
            'latitude' => 'float',
            'longitude' => 'float',
            'midtrans_server_key' => 'encrypted',
            'midtrans_is_production' => 'boolean',
            'midtrans_is_active' => 'boolean',
        ];
    }

    /**
     * Check if this TPS3R has active and complete Midtrans credentials.
     */
    public function isMidtransConfigured(): bool
    {
        return $this->midtrans_is_active && ! empty($this->midtrans_server_key) && ! empty($this->midtrans_client_key);
    }

    /**
     * Relation to User.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
