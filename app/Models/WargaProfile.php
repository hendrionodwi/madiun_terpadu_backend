<?php

namespace App\Models;

use Database\Factories\WargaProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $nik
 * @property string|null $alamat
 * @property string|null $rt
 * @property string|null $rw
 * @property string|null $kelurahan
 * @property string|null $kecamatan
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $saldo_poin
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'nik',
    'alamat',
    'rt',
    'rw',
    'kelurahan',
    'kecamatan',
    'latitude',
    'longitude',
    'saldo_poin',
])]
class WargaProfile extends Model
{
    /** @use HasFactory<WargaProfileFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'saldo_poin' => 'integer',
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
