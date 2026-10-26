<?php

namespace App\Models;

use Database\Factories\ComplaintVerificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $complaint_id
 * @property int $petugas_id
 * @property string $status_verifikasi
 * @property string $catatan_petugas
 * @property string|null $foto_bukti
 * @property Carbon $tanggal_verifikasi
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Complaint $complaint
 * @property-read User $petugas
 */
#[Fillable([
    'complaint_id',
    'petugas_id',
    'status_verifikasi',
    'catatan_petugas',
    'rekomendasi_tindak_lanjut',
    'foto_bukti',
    'tanggal_verifikasi',
])]
class ComplaintVerification extends Model
{
    /** @use HasFactory<ComplaintVerificationFactory> */
    use HasFactory;

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'foto_bukti_url',
    ];

    /**
     * Get the resolved public URL of the verification photo.
     */
    public function getFotoBuktiUrlAttribute(): ?string
    {
        if (! $this->foto_bukti) {
            return null;
        }

        if (str_starts_with($this->foto_bukti, 'http://') || str_starts_with($this->foto_bukti, 'https://')) {
            return $this->foto_bukti;
        }

        return url('api/v1/media/'.$this->foto_bukti);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    /**
     * Relation to Complaint.
     *
     * @return BelongsTo<Complaint, $this>
     */
    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'complaint_id');
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
}
