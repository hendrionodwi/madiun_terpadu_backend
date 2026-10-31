<?php

namespace App\Models;

use Database\Factories\RetributionBillFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nomor_tagihan
 * @property int $warga_id
 * @property int $periode_bulan
 * @property int $periode_tahun
 * @property float $nominal_tagihan
 * @property int $poin_digunakan
 * @property float $nominal_potongan_poin
 * @property float $nominal_dibayar
 * @property string $metode_pembayaran
 * @property string $status
 * @property Carbon $jatuh_tempo
 * @property Carbon|null $paid_at
 * @property string|null $midtrans_order_id
 * @property string|null $snap_token
 * @property string|null $snap_redirect_url
 * @property array<string, mixed>|null $payment_gateway_response
 * @property string|null $catatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $warga
 */
class RetributionBill extends Model
{
    /** @use HasFactory<RetributionBillFactory> */
    use HasFactory;

    /** @var list<string> */
    public const OUTSTANDING_STATUSES = ['belum_dibayar', 'menunggu_pembayaran'];

    protected $fillable = [
        'nomor_tagihan',
        'warga_id',
        'periode_bulan',
        'periode_tahun',
        'nominal_tagihan',
        'poin_digunakan',
        'nominal_potongan_poin',
        'nominal_dibayar',
        'metode_pembayaran',
        'status',
        'jatuh_tempo',
        'paid_at',
        'midtrans_order_id',
        'snap_token',
        'snap_redirect_url',
        'payment_gateway_response',
        'catatan',
    ];

    public function getRouteKeyName(): string
    {
        return 'nomor_tagihan';
    }

    protected function casts(): array
    {
        return [
            'periode_bulan' => 'integer',
            'periode_tahun' => 'integer',
            'nominal_tagihan' => 'float',
            'poin_digunakan' => 'integer',
            'nominal_potongan_poin' => 'float',
            'nominal_dibayar' => 'float',
            'jatuh_tempo' => 'date',
            'paid_at' => 'datetime',
            'payment_gateway_response' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function warga(): BelongsTo
    {
        return $this->belongsTo(User::class, 'warga_id');
    }

    public function isOverdue(?int $gracePeriodDays = null): bool
    {
        if (! in_array($this->status, self::OUTSTANDING_STATUSES, true)) {
            return false;
        }

        $grace = $gracePeriodDays ?? (int) static::getConfig('grace_period_days', 0);
        $cutoff = $this->jatuh_tempo->copy()->addDays($grace)->endOfDay();

        return $cutoff->isPast();
    }

    /**
     * @param  Builder<RetributionBill>  $query
     * @return Builder<RetributionBill>
     */
    public function scopeOverdue(Builder $query, ?int $gracePeriodDays = null): Builder
    {
        $grace = $gracePeriodDays ?? (int) static::getConfig('grace_period_days', 0);

        return $query
            ->whereIn('status', self::OUTSTANDING_STATUSES)
            ->where('jatuh_tempo', '<', now()->subDays($grace)->toDateString());
    }

    public function isPaid(): bool
    {
        return $this->status === 'lunas';
    }

    public function getPeriodeLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return ($months[$this->periode_bulan] ?? "Bulan {$this->periode_bulan}")." {$this->periode_tahun}";
    }

    /**
     * Get single retribution configuration item from MasterData.
     */
    public static function getConfig(string $key, mixed $default = null): mixed
    {
        $item = MasterData::where('group', 'retribution_config')
            ->where('code', $key)
            ->first();

        if (! $item) {
            return $default;
        }

        return $item->numeric_value ?? $item->description ?? $default;
    }

    /**
     * Get all retribution configurations as key-value array.
     *
     * @return array<string, mixed>
     */
    public static function getAllConfig(): array
    {
        $items = MasterData::where('group', 'retribution_config')
            ->get()
            ->keyBy('code');

        $pointConversion = MasterData::where('group', 'point_conversion')
            ->where('code', 'idr')
            ->first();

        return [
            'monthly_tariff' => (float) ($items['monthly_tariff']->numeric_value ?? 10000),
            'standard_due_day' => (int) ($items['standard_due_day']->numeric_value ?? 20),
            'max_point_discount_percent' => (float) ($items['max_point_discount_percent']->numeric_value ?? 100),
            'grace_period_days' => (int) ($items['grace_period_days']->numeric_value ?? 0),
            'reminder_template' => (string) ($items['reminder_template']->description ?? 'Yth. Bapak/Ibu {nama}, tagihan retribusi kebersihan Kota Madiun periode {periode} sebesar Rp {nominal} (Jatuh Tempo: {jatuh_tempo}) {status_info}. Silakan lakukan pelunasan via aplikasi Madiun Terpadu (bisa menggunakan Poin Bank Sampah atau QRIS/E-Wallet). Terima kasih - DLH Kota Madiun.'),
            'point_conversion_rate' => (float) ($pointConversion->numeric_value ?? 10),
        ];
    }

    /**
     * Generate customized WhatsApp reminder message for this bill.
     */
    public function generateWhatsAppReminderMessage(?string $template = null): string
    {
        $template = $template ?? (string) static::getConfig('reminder_template', 'Yth. Bapak/Ibu {nama}, tagihan retribusi kebersihan Kota Madiun periode {periode} sebesar Rp {nominal} (Jatuh Tempo: {jatuh_tempo}) {status_info}. Silakan lakukan pelunasan via aplikasi Madiun Terpadu (bisa menggunakan Poin Bank Sampah atau QRIS/E-Wallet). Terima kasih - DLH Kota Madiun.');

        $isOverdue = $this->isOverdue();
        $statusInfo = $isOverdue
            ? 'telah MELEWATI JATUH TEMPO (Layanan penjemputan sampah saat ini ditangguhkan)'
            : 'saat ini menunggu pembayaran';

        $wargaName = $this->warga->name;
        $formattedNominal = number_format($this->nominal_tagihan, 0, ',', '.');
        $formattedJatuhTempo = $this->jatuh_tempo->format('d/m/Y');

        return strtr($template, [
            '{nama}' => $wargaName,
            '{periode}' => $this->periode_label,
            '{nominal}' => $formattedNominal,
            '{nomor_tagihan}' => $this->nomor_tagihan,
            '{jatuh_tempo}' => $formattedJatuhTempo,
            '{status_info}' => $statusInfo,
        ]);
    }

    /**
     * Generate click-to-chat WhatsApp URL for this bill.
     */
    public function getWhatsAppUrlAttribute(): ?string
    {
        $phone = $this->warga->phone_number;
        if (! $phone) {
            return null;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        } elseif (! str_starts_with($cleanPhone, '62')) {
            $cleanPhone = '62'.$cleanPhone;
        }

        $message = rawurlencode($this->generateWhatsAppReminderMessage());

        return "https://wa.me/{$cleanPhone}?text={$message}";
    }

    /**
     * Generate unique retribution ticket/invoice number.
     */
    public static function generateNomorTagihan(int $year, int $month, int $wargaId): string
    {
        $prefix = sprintf('RET-%04d%02d', $year, $month);
        $random = strtoupper(bin2hex(random_bytes(2)));

        return "{$prefix}-{$wargaId}-{$random}";
    }
}
