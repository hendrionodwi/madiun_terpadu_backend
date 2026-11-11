<?php

namespace App\Models;

use Database\Factories\PengelolaTps3rProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $public_id
 * @property int $user_id
 * @property string $nama_tps3r
 * @property string|null $kode_tps3r
 * @property string|null $alamat
 * @property string|null $kelurahan
 * @property string|null $kecamatan
 * @property float|null $kapasitas_harian_kg
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $midtrans_merchant_id
 * @property string|null $midtrans_client_key
 * @property string|null $midtrans_server_key
 * @property string|null $midtrans_signature_secret
 * @property bool $midtrans_is_production
 * @property bool $midtrans_is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 */
#[Fillable([
    'user_id',
    'nama_tps3r',
    'kode_tps3r',
    'jenis_fasilitas',
    'alamat',
    'kelurahan',
    'kecamatan',
    'jam_operasional',
    'layanan_tersedia',
    'kategori_sampah_diterima',
    'fokus_pengolahan',
    'cakupan_wilayah',
    'nama_koordinator',
    'foto_fasilitas',
    'is_featured_portal',
    'kapasitas_harian_kg',
    'latitude',
    'longitude',
    'midtrans_merchant_id',
    'midtrans_client_key',
    'midtrans_server_key',
    'midtrans_signature_secret',
    'midtrans_is_production',
    'midtrans_is_active',
])]
#[Hidden(['midtrans_server_key', 'midtrans_signature_secret'])]
class PengelolaTps3rProfile extends Model
{
    /** @use HasFactory<PengelolaTps3rProfileFactory> */
    use HasFactory, HasUlids;

    /** @var array<int, string> */
    protected $appends = [
        'foto_url',
    ];

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
            'kapasitas_harian_kg' => 'float',
            'latitude' => 'float',
            'longitude' => 'float',
            'layanan_tersedia' => 'array',
            'kategori_sampah_diterima' => 'array',
            'is_featured_portal' => 'boolean',
            'midtrans_server_key' => 'encrypted',
            'midtrans_signature_secret' => 'encrypted',
            'midtrans_is_production' => 'boolean',
            'midtrans_is_active' => 'boolean',
        ];
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto_fasilitas) {
            return null;
        }

        if (str_starts_with($this->foto_fasilitas, 'http://') || str_starts_with($this->foto_fasilitas, 'https://')) {
            return $this->foto_fasilitas;
        }

        return asset('storage/'.ltrim($this->foto_fasilitas, '/'));
    }

    /**
     * Check if this TPS3R has active and complete Midtrans credentials.
     */
    public function isMidtransConfigured(): bool
    {
        return $this->midtrans_is_active && ! empty($this->midtrans_server_key) && ! empty($this->midtrans_client_key);
    }

    /**
     * Resolve the webhook signing secret used by the current Midtrans account.
     *
     * New Midtrans accounts expose a dedicated Signature Secret Key, while
     * legacy accounts sign notifications with the Server Key.
     */
    public function webhookSignatureSecret(): string
    {
        $signatureSecret = trim((string) $this->midtrans_signature_secret);

        return $signatureSecret !== ''
            ? $signatureSecret
            : trim((string) $this->midtrans_server_key);
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
