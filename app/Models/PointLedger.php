<?php

namespace App\Models;

use Database\Factories\PointLedgerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * @property int $id
 * @property int $warga_id
 * @property string $tipe
 * @property string $kategori
 * @property int $jumlah_poin
 * @property int $saldo_sebelumnya
 * @property int $saldo_setelahnya
 * @property string|null $referensi_tipe
 * @property int|null $referensi_id
 * @property string $deskripsi
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $warga
 * @property-read Model|null $referensi
 */
class PointLedger extends Model
{
    /** @use HasFactory<PointLedgerFactory> */
    use HasFactory;

    protected $fillable = [
        'warga_id',
        'tipe',
        'kategori',
        'jumlah_poin',
        'saldo_sebelumnya',
        'saldo_setelahnya',
        'referensi_tipe',
        'referensi_id',
        'deskripsi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_poin' => 'integer',
            'saldo_sebelumnya' => 'integer',
            'saldo_setelahnya' => 'integer',
            'referensi_id' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function warga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warga_id');
    }

    /** @return MorphTo<Model, $this> */
    public function referensi(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'referensi_tipe', 'referensi_id');
    }

    /**
     * Record a point mutation atomically into the ledger and update WargaProfile balance.
     *
     * @param  'setor_sampah'|'belanja_marketplace'|'refund_marketplace'|'penukaran_poin'|'refund_penukaran_poin'|'bayar_retribusi'|'refund_retribusi'|'penyesuaian_admin'  $kategori
     */
    public static function record(
        int $wargaId,
        string $tipe,
        string $kategori,
        int $jumlahPoin,
        string $deskripsi,
        ?Model $referensi = null
    ): self {
        if ($jumlahPoin <= 0) {
            throw new InvalidArgumentException('Jumlah poin harus lebih besar dari 0.');
        }

        if (! in_array($tipe, ['kredit', 'debit'], true)) {
            throw new InvalidArgumentException("Tipe mutasi tidak valid: {$tipe}.");
        }

        return DB::transaction(function () use ($wargaId, $tipe, $kategori, $jumlahPoin, $deskripsi, $referensi) {
            /** @var WargaProfile $profile */
            $profile = WargaProfile::firstOrCreate(
                ['user_id' => $wargaId],
                ['saldo_poin' => 0]
            );

            // Lock profile row
            $profile = WargaProfile::where('id', $profile->id)->lockForUpdate()->first();
            $saldoSebelumnya = (int) $profile->saldo_poin;

            if ($tipe === 'debit') {
                if ($saldoSebelumnya < $jumlahPoin) {
                    throw new InvalidArgumentException("Saldo poin warga ({$saldoSebelumnya}) tidak mencukupi untuk pengurangan sebesar {$jumlahPoin} poin.");
                }
                $saldoSetelahnya = $saldoSebelumnya - $jumlahPoin;
                $profile->decrement('saldo_poin', $jumlahPoin);
            } else {
                $saldoSetelahnya = $saldoSebelumnya + $jumlahPoin;
                $profile->increment('saldo_poin', $jumlahPoin);
            }

            return self::create([
                'warga_id' => $wargaId,
                'tipe' => $tipe,
                'kategori' => $kategori,
                'jumlah_poin' => $jumlahPoin,
                'saldo_sebelumnya' => $saldoSebelumnya,
                'saldo_setelahnya' => $saldoSetelahnya,
                'referensi_tipe' => $referensi ? get_class($referensi) : null,
                'referensi_id' => $referensi?->getKey(),
                'deskripsi' => $deskripsi,
            ]);
        });
    }
}
