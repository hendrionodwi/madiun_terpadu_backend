<?php

namespace App\Models;

use Database\Factories\WasteWeighingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $warga_id
 * @property int $petugas_id
 * @property int|null $pickup_schedule_id
 * @property string $jenis_sampah
 * @property float $berat_kg
 * @property int $poin_per_kg
 * @property int $poin_didapat
 * @property string|null $catatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $warga
 * @property-read User $petugas
 * @property-read PickupSchedule|null $pickupSchedule
 */
#[Fillable([
    'warga_id',
    'petugas_id',
    'pickup_schedule_id',
    'tps3r_waste_deposit_id',
    'jenis_sampah',
    'berat_kg',
    'poin_per_kg',
    'poin_didapat',
    'catatan',
    'foto_bukti',
])]
class WasteWeighing extends Model
{
    /** @use HasFactory<WasteWeighingFactory> */
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
            'poin_per_kg' => 'integer',
            'poin_didapat' => 'integer',
        ];
    }

    /**
     * Relation to Warga.
     *
     * @return BelongsTo<User, $this>
     */
    public function warga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warga_id');
    }

    /**
     * Relation to Petugas.
     *
     * @return BelongsTo<User, $this>
     */
    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Relation to Pickup Schedule.
     *
     * @return BelongsTo<PickupSchedule, $this>
     */
    public function pickupSchedule(): BelongsTo
    {
        return $this->belongsTo(PickupSchedule::class, 'pickup_schedule_id');
    }

    /**
     * Relation to TPS3R Waste Deposit.
     *
     * @return BelongsTo<Tps3rWasteDeposit, $this>
     */
    public function tps3rDeposit(): BelongsTo
    {
        return $this->belongsTo(Tps3rWasteDeposit::class, 'tps3r_waste_deposit_id');
    }
}
