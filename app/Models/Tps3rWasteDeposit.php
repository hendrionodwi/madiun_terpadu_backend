<?php

namespace App\Models;

use Database\Factories\Tps3rWasteDepositFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pengelola_id
 * @property int|null $tps3r_profile_id
 * @property int|null $petugas_id
 * @property string $nomor_resi
 * @property string $sumber_sampah
 * @property string $jenis_sampah
 * @property float $berat_kg
 * @property string $status
 * @property float|null $berat_konfirmasi_kg
 * @property Carbon|null $verified_at
 * @property string|null $catatan_verifikasi
 * @property Carbon $tanggal_setor
 * @property string|null $catatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $pengelola
 * @property-read PengelolaTps3rProfile|null $tps3rProfile
 * @property-read User|null $petugas
 */
#[Fillable([
    'pengelola_id',
    'tps3r_profile_id',
    'petugas_id',
    'nomor_resi',
    'route_code',
    'sumber_sampah',
    'jenis_sampah',
    'berat_kg',
    'status',
    'berat_konfirmasi_kg',
    'verified_at',
    'catatan_verifikasi',
    'berat_organik_pilah_kg',
    'berat_anorganik_pilah_kg',
    'berat_residu_pilah_kg',
    'tanggal_setor',
    'catatan',
])]
class Tps3rWasteDeposit extends Model
{
    /** @use HasFactory<Tps3rWasteDepositFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'berat_kg' => 'float',
            'berat_konfirmasi_kg' => 'float',
            'berat_organik_pilah_kg' => 'float',
            'berat_anorganik_pilah_kg' => 'float',
            'berat_residu_pilah_kg' => 'float',
            'verified_at' => 'datetime',
            'tanggal_setor' => 'date',
        ];
    }

    public function isVerified(): bool
    {
        return $this->status === 'diterima';
    }

    public function isPending(): bool
    {
        return $this->status === 'menunggu_konfirmasi';
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'nomor_resi';
    }

    /**
     * Relation to Pengelola User.
     *
     * @return BelongsTo<User, $this>
     */
    public function pengelola(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengelola_id');
    }

    /**
     * Relation to TPS3R Profile.
     *
     * @return BelongsTo<PengelolaTps3rProfile, $this>
     */
    public function tps3rProfile(): BelongsTo
    {
        return $this->belongsTo(PengelolaTps3rProfile::class, 'tps3r_profile_id');
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
     * Relation to Waste Weighings.
     *
     * @return HasMany<WasteWeighing, $this>
     */
    public function weighings(): HasMany
    {
        return $this->hasMany(WasteWeighing::class, 'tps3r_waste_deposit_id');
    }
}
