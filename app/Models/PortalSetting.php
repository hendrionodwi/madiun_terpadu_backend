<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property string $group
 * @property string $label
 * @property string $type
 * @property string|null $value
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $updater
 */
#[Fillable([
    'key',
    'group',
    'label',
    'type',
    'value',
    'updated_by',
])]
class PortalSetting extends Model
{
    /**
     * Default values used as clean fallback before seeding or during fresh test runs.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'hero_kicker' => 'Portal Layanan Terpadu DLH Kota Madiun',
            'hero_title' => 'Bersama Menjaga Madiun Bersih dan Berkelanjutan',
            'hero_subtitle' => 'Akses informasi penjemputan sampah pilah, pengaduan kebersihan lingkungan, pemetaan TPS3R & Bank Sampah, edukasi daur ulang, serta katalog produk ekonomi sirkular Kota Madiun dalam satu portal pelayanan terpadu.',
            'hero_badge_text' => 'Pencatatan Manual Resmi · 3 Kecamatan Terpadu · Pengelolaan Sirkular',
            'operational_status_label' => 'Pelayanan Wilayah Normal',
            'contact_address' => 'Jl. Kolonel Marhadi No. 1, Kota Madiun, Jawa Timur 63117',
            'contact_email' => 'dlh@madiunkota.go.id',
            'contact_phone' => '(0351) 462748',
            'transparency_note' => 'Layanan ini mengandalkan validasi faktual petugas lapangan dan operator TPS3R resmi, bukan pemantauan telemetri otomatis tanpa verifikasi.',
            'announcement_banner_enabled' => '1',
            'show_stats_section' => '1',
            'show_education_section' => '1',
            'show_marketplace_section' => '1',
            'service_steps' => [
                [
                    'step' => 1,
                    'title' => 'Pilah Sampah',
                    'description' => 'Pisahkan sampah organik, anorganik daur ulang, dan residu sejak dari sumber rumah tangga.',
                ],
                [
                    'step' => 2,
                    'title' => 'Ajukan Layanan',
                    'description' => 'Warga terdaftar memesan penjemputan berkala atau mengunjungi titik pengantaran TPS3R.',
                ],
                [
                    'step' => 3,
                    'title' => 'Petugas Menjemput',
                    'description' => 'Armada pengangkut mengambil wadah sesuai jadwal dan rute wilayah terjadwal.',
                ],
                [
                    'step' => 4,
                    'title' => 'TPS3R Verifikasi',
                    'description' => 'Petugas fasilitas menimbang ulang dan memverifikasi kualitas pemilahan secara langsung.',
                ],
                [
                    'step' => 5,
                    'title' => 'Sampah Diolah',
                    'description' => 'Organik diolah menjadi kompos/maggot dan anorganik bernilai ekonomi disalurkan ke rantai daur ulang.',
                ],
            ],
        ];
    }

    /**
     * Resolve all portal settings merged with safe defaults.
     *
     * @return array<string, mixed>
     */
    public static function resolved(): array
    {
        $defaults = self::defaults();
        $rows = static::query()->get(['key', 'type', 'value']);

        foreach ($rows as $row) {
            if ($row->type === 'json') {
                $decoded = json_decode((string) $row->value, true);
                if (is_array($decoded)) {
                    $defaults[$row->key] = $decoded;
                }
            } elseif ($row->type === 'boolean') {
                $defaults[$row->key] = in_array((string) $row->value, ['1', 'true', 'yes'], true) ? '1' : '0';
            } else {
                if ($row->value !== null && $row->value !== '') {
                    $defaults[$row->key] = $row->value;
                }
            }
        }

        return $defaults;
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
