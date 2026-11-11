<?php

namespace App\Models;

use Database\Factories\EnvironmentalReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $public_id
 * @property string|null $nomor_laporan
 * @property int $pemrakarsa_id
 * @property string $judul_laporan
 * @property string $periode_semester
 * @property int $tahun
 * @property string $jenis_dokumen
 * @property float|null $timbulan_sampah_kg
 * @property string|null $metode_pengelolaan
 * @property array<string, mixed>|null $monitoring_data
 * @property string|null $file_path
 * @property string $status
 * @property string|null $nomor_bap
 * @property string|null $catatan_verifikasi
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $pemrakarsa
 */
#[Fillable([
    'nomor_laporan',
    'pemrakarsa_id',
    'assigned_petugas_id',
    'judul_laporan',
    'periode_semester',
    'tahun',
    'jenis_dokumen',
    'timbulan_sampah_kg',
    'metode_pengelolaan',
    'monitoring_data',
    'file_path',
    'status',
    'nomor_bap',
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
    use HasFactory, HasUlids;

    protected static function booted(): void
    {
        static::creating(function (EnvironmentalReport $report): void {
            if (empty($report->nomor_laporan)) {
                $report->nomor_laporan = 'LAP-'.($report->tahun ?? date('Y')).'-'.strtoupper(Str::random(6));
            }
        });
    }

    /** @return list<string> */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

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
            'monitoring_data' => 'array',
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
