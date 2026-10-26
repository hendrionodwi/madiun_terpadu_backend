<?php

namespace Database\Seeders;

use App\Models\MasterData;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['group' => 'waste_category', 'code' => 'organik', 'pillar' => 'organik', 'name' => 'Sampah Organik', 'numeric_value' => 100, 'unit' => 'poin/kg', 'description' => 'Sisa makanan dan daun. Pisahkan dari plastik lalu olah menjadi kompos.', 'sort_order' => 1],
            ['group' => 'waste_category', 'code' => 'anorganik_plastik', 'pillar' => 'anorganik_daur_ulang', 'name' => 'Plastik', 'numeric_value' => 250, 'unit' => 'poin/kg', 'description' => 'Botol dan kemasan plastik bersih. Bilas, keringkan, lalu pipihkan.', 'sort_order' => 2],
            ['group' => 'waste_category', 'code' => 'anorganik_kertas', 'pillar' => 'anorganik_daur_ulang', 'name' => 'Kertas dan Kardus', 'numeric_value' => 150, 'unit' => 'poin/kg', 'description' => 'Kertas, koran, dan kardus yang kering serta tidak terkena minyak.', 'sort_order' => 3],
            ['group' => 'waste_category', 'code' => 'anorganik_logam', 'pillar' => 'anorganik_daur_ulang', 'name' => 'Logam', 'numeric_value' => 400, 'unit' => 'poin/kg', 'description' => 'Kaleng dan logam kecil. Bersihkan bagian tajam sebelum disetor.', 'sort_order' => 4],
            ['group' => 'waste_category', 'code' => 'residu', 'pillar' => 'residu', 'name' => 'Residu', 'numeric_value' => 0, 'unit' => 'poin/kg', 'description' => 'Popok, tisu kotor, dan material yang belum dapat didaur ulang. Bungkus rapat.', 'sort_order' => 5],
            ['group' => 'point_conversion', 'code' => 'idr', 'name' => 'Konversi Rupiah', 'numeric_value' => 10, 'unit' => 'rupiah/poin', 'description' => 'Nilai penukaran satu poin menjadi rupiah.', 'sort_order' => 1],
            ['group' => 'complaint_type', 'code' => 'sampah_liar', 'name' => 'Tumpukan Sampah Liar', 'numeric_value' => null, 'unit' => null, 'description' => 'Aduan tumpukan sampah di luar titik resmi.', 'sort_order' => 1],
            ['group' => 'complaint_type', 'code' => 'pencemaran', 'name' => 'Pencemaran Lingkungan', 'numeric_value' => null, 'unit' => null, 'description' => 'Aduan pencemaran air, tanah, atau udara.', 'sort_order' => 2],
            ['group' => 'retribution_config', 'code' => 'monthly_tariff', 'name' => 'Tarif Retribusi Bulanan', 'numeric_value' => 10000, 'unit' => 'rupiah/bulan', 'description' => 'Tarif retribusi pelayanan persampahan dan kebersihan warga Kota Madiun per bulan.', 'sort_order' => 1],
            ['group' => 'retribution_config', 'code' => 'standard_due_day', 'name' => 'Tanggal Standar Jatuh Tempo', 'numeric_value' => 20, 'unit' => 'tanggal', 'description' => 'Batas tanggal jatuh tempo pembayaran retribusi setiap bulannya (1-31).', 'sort_order' => 2],
            ['group' => 'retribution_config', 'code' => 'max_point_discount_percent', 'name' => 'Maksimal Potongan Poin', 'numeric_value' => 100, 'unit' => '%', 'description' => 'Batas maksimal persentase tagihan yang dapat dibayar/dipotong menggunakan poin sampah warga.', 'sort_order' => 3],
            ['group' => 'retribution_config', 'code' => 'grace_period_days', 'name' => 'Toleransi Hari Keterlambatan', 'numeric_value' => 0, 'unit' => 'hari', 'description' => 'Jumlah hari toleransi setelah jatuh tempo sebelum layanan penjemputan sampah warga dikunci.', 'sort_order' => 4],
            ['group' => 'retribution_config', 'code' => 'reminder_template', 'name' => 'Template Pesan Pengingat Tagihan', 'numeric_value' => null, 'unit' => 'teks', 'description' => 'Yth. Bapak/Ibu {nama}, tagihan retribusi kebersihan Kota Madiun periode {periode} sebesar Rp {nominal} (Jatuh Tempo: {jatuh_tempo}) {status_info}. Silakan lakukan pelunasan via aplikasi Madiun Terpadu (bisa menggunakan Poin Bank Sampah atau QRIS/E-Wallet). Terima kasih - DLH Kota Madiun.', 'sort_order' => 5],
        ];

        foreach ($items as $item) {
            MasterData::updateOrCreate(
                ['group' => $item['group'], 'code' => $item['code']],
                [...$item, 'is_active' => true],
            );
        }
    }
}
