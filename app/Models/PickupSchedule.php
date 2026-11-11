<?php

namespace App\Models;

use Database\Factories\PickupScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $kode_jadwal
 * @property int $petugas_id
 * @property int|null $warga_id
 * @property string $id_wilayah_rute
 * @property string|null $nama_wilayah_rute
 * @property string|null $jenis_sampah
 * @property float|null $estimasi_berat_kg
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
    'kode_jadwal',
    'petugas_id',
    'warga_id',
    'id_wilayah_rute',
    'nama_wilayah_rute',
    'jenis_sampah',
    'estimasi_berat_kg',
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

    protected static function booted(): void
    {
        static::creating(function (PickupSchedule $schedule): void {
            if (empty($schedule->kode_jadwal)) {
                $datePart = $schedule->tanggal_penjemputan
                    ? Carbon::parse($schedule->tanggal_penjemputan)->format('Ymd')
                    : date('Ymd');
                $schedule->kode_jadwal = 'JMP-'.$datePart.'-'.strtoupper(Str::random(6));
            }
        });
    }

    /**
     * Resolve pickup schedule by either kode_jadwal or numeric id for backwards compatibility.
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($field !== null) {
            return parent::resolveRouteBinding($value, $field);
        }

        if (is_numeric($value)) {
            return $this->whereKey((int) $value)->first();
        }

        return $this->where('kode_jadwal', $value)->first();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_penjemputan' => 'date',
            'estimasi_berat_kg' => 'float',
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
