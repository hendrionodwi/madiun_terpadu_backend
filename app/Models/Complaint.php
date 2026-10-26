<?php

namespace App\Models;

use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nomor_tiket
 * @property int $pelapor_id
 * @property string $judul
 * @property string $deskripsi
 * @property string $lokasi
 * @property string|null $kelurahan
 * @property string|null $kecamatan
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $foto
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $pelapor
 * @property-read Collection<int, ComplaintVerification> $verifications
 */
#[Fillable([
    'nomor_tiket',
    'pelapor_id',
    'assigned_petugas_id',
    'judul',
    'kategori_masalah',
    'deskripsi',
    'lokasi',
    'kelurahan',
    'kecamatan',
    'latitude',
    'longitude',
    'foto',
    'is_anonim',
    'status',
    'instruksi_admin',
    'assigned_at',
])]
class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'foto_url',
        'status_label',
        'display_pelapor_name',
    ];

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
            'is_anonim' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    /**
     * Get human-readable status label according to SRS PPL-F-002.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'diterima', 'menunggu_verifikasi' => 'Diterima',
            'ditugaskan' => 'Ditugaskan ke Petugas Lapangan',
            'diverifikasi_lapangan' => 'Diverifikasi Lapangan',
            'ditindaklanjuti', 'dalam_proses' => 'Ditindaklanjuti',
            'selesai' => 'Selesai Ditangani',
            'ditolak' => 'Ditolak',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Get display pelapor name respecting anonymity (PPL-F-007).
     */
    public function getDisplayPelaporNameAttribute(): string
    {
        if ($this->is_anonim) {
            return 'Warga Anonim (Identitas Dirahasiakan)';
        }

        return $this->pelapor->name ?? 'Warga';
    }

    /**
     * Relation to ComplaintStatusLogs (PPL-F-006).
     *
     * @return HasMany<ComplaintStatusLog, $this>
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(ComplaintStatusLog::class, 'complaint_id')->latest();
    }

    /**
     * Get the resolved public URL of the complaint photo.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto) {
            return null;
        }

        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        return url('api/v1/media/'.$this->foto);
    }

    /**
     * Relation to Pelapor User.
     *
     * @return BelongsTo<User, $this>
     */
    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelapor_id');
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

    /**
     * Relation to Verifications.
     *
     * @return HasMany<ComplaintVerification, $this>
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(ComplaintVerification::class, 'complaint_id');
    }
}
