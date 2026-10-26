<?php

namespace App\Models;

use Database\Factories\EnvironmentalReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $pemrakarsa_id
 * @property string $judul_laporan
 * @property string $periode_semester
 * @property int $tahun
 * @property string $jenis_dokumen
 * @property float|null $timbulan_sampah_kg
 * @property string|null $metode_pengelolaan
 * @property string $file_path
 * @property string $status
 * @property string|null $catatan_verifikasi
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $pemrakarsa
 */
#[Fillable([
    'pemrakarsa_id',
    'assigned_petugas_id',
    'judul_laporan',
    'periode_semester',
    'tahun',
    'jenis_dokumen',
    'timbulan_sampah_kg',
    'metode_pengelolaan',
    'file_path',
    'status',
    'catatan_verifikasi',
    'instruksi_lapangan',
    'catatan_inspeksi_petugas',
    'hasil_inspeksi_lapangan',
    'assigned_at',
    'inspeksi_at',
    'verified_at',
])]
class EnvironmentalReport extends Model
{
    /** @use HasFactory<EnvironmentalReportFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'timbulan_sampah_kg' => 'float',
            'verified_at' => 'datetime',
            'assigned_at' => 'datetime',
            'inspeksi_at' => 'datetime',
        ];
    }

    /**
     * Relation to Pemrakarsa User.
     *
     * @return BelongsTo<User, $this>
     */
    public function pemrakarsa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemrakarsa_id');
    }

    /**
     * Relation to Assigned Petugas User.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignedPetugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_petugas_id');
    }
}
