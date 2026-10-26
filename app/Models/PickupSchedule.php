<?php

namespace App\Models;

use Database\Factories\PickupScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $petugas_id
 * @property int|null $warga_id
 * @property string $id_wilayah_rute
 * @property string|null $nama_wilayah_rute
 * @property string|null $jenis_sampah
 * @property Carbon $tanggal_penjemputan
 * @property string $waktu_mulai
 * @property string $waktu_selesai
 * @property string $status
 * @property string|null $keterangan
 * @property string|null $alamat_penjemputan
 * @property float|null $latitude
 * @property float|null $longitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $petugas
 * @property-read User|null $warga
 */
#[Fillable([
    'petugas_id',
    'warga_id',
    'id_wilayah_rute',
    'nama_wilayah_rute',
    'jenis_sampah',
    'tanggal_penjemputan',
    'waktu_mulai',
    'waktu_selesai',
    'status',
    'keterangan',
    'alamat_penjemputan',
    'latitude',
    'longitude',
])]
class PickupSchedule extends Model
{
    /** @use HasFactory<PickupScheduleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_penjemputan' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Relation to Petugas User.
     *
     * @return BelongsTo<User, $this>
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Relation to Warga User.
     *
     * @return BelongsTo<User, $this>
     */
    public function warga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warga_id');
    }

    /**
     * Relation to Waste Weighings.
     *
     * @return HasMany<WasteWeighing, $this>
     */
    public function weighings(): HasMany
    {
        return $this->hasMany(WasteWeighing::class, 'pickup_schedule_id');
    }
}
