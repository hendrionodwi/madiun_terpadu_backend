<?php

namespace App\Models;

use Database\Factories\PemrakarsaProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $nama_instansi
 * @property string|null $jenis_instansi
 * @property string|null $nib
 * @property string|null $penanggung_jawab
 * @property string|null $alamat_instansi
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'nama_instansi',
    'jenis_instansi',
    'nib',
    'penanggung_jawab',
    'alamat_instansi',
    'latitude',
    'longitude',
])]
class PemrakarsaProfile extends Model
{
    /** @use HasFactory<PemrakarsaProfileFactory> */
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
