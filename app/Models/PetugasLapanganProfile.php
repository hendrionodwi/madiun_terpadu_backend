<?php

namespace App\Models;

use Database\Factories\PetugasLapanganProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $nip
 * @property string|null $id_wilayah_rute
 * @property string|null $nama_wilayah_tugas
 * @property string|null $jenis_kendaraan
 * @property string|null $plat_nomor
 * @property string $status_tugas
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'nip',
    'id_wilayah_rute',
    'nama_wilayah_tugas',
    'jenis_kendaraan',
    'plat_nomor',
    'status_tugas',
])]
class PetugasLapanganProfile extends Model
{
    /** @use HasFactory<PetugasLapanganProfileFactory> */
    use HasFactory;

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
