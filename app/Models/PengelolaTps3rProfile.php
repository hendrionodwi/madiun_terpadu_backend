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
        ];
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
